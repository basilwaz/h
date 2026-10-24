#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://localhost:8080}"
: "${API_TOKEN:?Set API_TOKEN to the server token first.}"
result=$(mktemp)
trap 'rm -f "$result"' EXIT
count=0
request() {
    local expected="$1" method="$2" path="$3"
    shift 3
    local status
    status=$(curl -sS -o "$result" -w '%{http_code}' -X "$method" "$BASE_URL$path" "$@")
    if [[ "$status" != "$expected" ]]; then
        echo "FAIL $method $path: expected $expected, got $status"
        cat "$result"
        exit 1
    fi
    count=$((count+1))
    echo "PASS $method $path ($status)"
}
field() { php -r '$j=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR); echo $j[$argv[2]];' "$result" "$1"; }
json=(-H 'Content-Type: application/json')
auth=(-H "Authorization: Bearer $API_TOKEN")
request 200 GET /genres
request 201 POST /genres "${json[@]}" -d "{\"name\":\"Test genre $(date +%s)-$$\"}"
genre=$(field id)
request 200 GET "/genres/$genre"
request 200 PUT "/genres/$genre" "${json[@]}" -d "{\"name\":\"Updated genre $(date +%s)-$$\"}"
request 201 POST /movies "${json[@]}" -d "{\"title\":\"Test Movie\",\"release_year\":2020,\"genre_id\":$genre}"
movie=$(field id)
request 200 GET /movies
request 200 GET "/movies/$movie"
[[ "$(field title)" == 'Test Movie' ]] || exit 1
request 200 PUT "/movies/$movie" "${json[@]}" -d "{\"title\":\"Updated Movie\",\"release_year\":2021,\"genre_id\":$genre}"
[[ "$(field title)" == 'Updated Movie' ]] || exit 1
request 200 GET "/movies/$movie"
[[ "$(field release_year)" == '2021' ]] || exit 1
for path in "/movies/$movie" "/genres/$genre"; do
    request 401 DELETE "$path"
    request 401 DELETE "$path" -H 'Authorization: Bearer wrong-token'
    request 200 GET "$path"
done
request 409 DELETE "/genres/$genre" "${auth[@]}"
request 422 POST /movies "${json[@]}" -d '{"title":"Bad","release_year":1800,"genre_id":1}'
request 422 POST /movies "${json[@]}" -d '{"title":"Bad","release_year":2020,"genre_id":2147483647}'
request 422 POST /genres "${json[@]}" -d '{"name":""}'
request 400 POST /genres "${json[@]}" -d '{bad'
request 400 POST /genres "${json[@]}" -d '[]'
request 415 POST /genres -d 'name=wrong'
request 405 PATCH "/movies/$movie" "${json[@]}" -d '{}'
request 404 GET /unknown
request 404 GET /movies/2147483647
request 200 DELETE "/movies/$movie" "${auth[@]}"
request 404 GET "/movies/$movie"
request 200 DELETE "/genres/$genre" "${auth[@]}"
request 404 GET "/genres/$genre"
echo "$count checks passed. Temporary test records deleted."
