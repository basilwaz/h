# Movie Collection API

Basil Wazwaz | ASE 230 | Project 1

This API keeps a small movie collection. Each movie has a title, release year, and genre. It uses PHP, PDO, and MySQL. There are 10 endpoints, including two delete routes that need a Bearer token.

## Files

- `code/`: API, database schema, cURL tests, browser tests, and an NGINX config example.
- `presentation/`: API tutorial and deployment slides in Marp and PDF, plus deployment screenshots.
- `plan/`: updated HW2 plan and the original plan for reference.
- `project1_rubric.md`: self-assessment with verified results and personal checks left for review.

## Requirements

PHP 8.1 or newer with `pdo_mysql`, MySQL 8 or newer, cURL, and Bash are needed. The cURL tests also use PHP to read JSON. NGINX and PHP-FPM are needed for the deployment section. The API has no Composer packages or paid API keys.

On Ubuntu 24.04:

```bash
sudo apt update
sudo apt install php-cli php-mysql mysql-server curl nginx php-fpm
sudo systemctl start mysql
php -v
php -m | grep -E 'PDO|pdo_mysql'
```

## Set up the database

Run these commands from the folder containing this README:

```bash
sudo mysql < code/schema.sql
sudo mysql
```

In the MySQL prompt, create a local application user. Choose your own password in place of `YOUR_LOCAL_PASSWORD`:

```sql
CREATE USER 'movie_app'@'127.0.0.1' IDENTIFIED BY 'YOUR_LOCAL_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON movie_collection.*
TO 'movie_app'@'127.0.0.1';
EXIT;
```

If your installation uses a MySQL root password, use `mysql -u root -p` instead of `sudo mysql`.

`schema.sql` creates `movie_collection`, the `genres` and `movies` tables, two genres, and two movies. Import it once into a fresh database. It does not drop existing data. Reimporting it reports that the tables already exist. For a full reset, back up your work, deliberately drop only `movie_collection`, and import the schema again.

## Run the PHP server

In the same terminal, set your local values. The password must match the database user above. Choose a long random token and keep it local.

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_USER=movie_app
read -rsp 'Database password: ' DB_PASSWORD; echo
export DB_PASSWORD
export API_TOKEN="$(php -r 'echo bin2hex(random_bytes(24));')"
cd code
php -S localhost:8080 router.php
```

Open <http://localhost:8080/test_client.html>. Do not open the HTML file directly from Finder or your file manager. The router lets the server handle the API routes and serves the test page.

Press **Ctrl+C** in the server terminal to stop it.

## Endpoints

| Method | Path | Purpose | Token |
|---|---|---|---|
| GET | `/genres` | List genres | No |
| GET | `/genres/{id}` | Get one genre | No |
| POST | `/genres` | Add a genre | No |
| PUT | `/genres/{id}` | Replace a genre's name | No |
| DELETE | `/genres/{id}` | Delete an unused genre | Yes |
| GET | `/movies` | List movies | No |
| GET | `/movies/{id}` | Get one movie | No |
| POST | `/movies` | Add a movie | No |
| PUT | `/movies/{id}` | Replace a movie's fields | No |
| DELETE | `/movies/{id}` | Delete a movie | Yes |

Send JSON with `Content-Type: application/json` for POST and PUT. PUT needs all the fields. Extra fields are ignored. IDs are positive integers. New records get an auto-generated ID.

```bash
curl http://localhost:8080/movies
curl -i -X POST http://localhost:8080/genres \
  -H 'Content-Type: application/json' -d '{"name":"Comedy"}'
curl -i -X POST http://localhost:8080/movies \
  -H 'Content-Type: application/json' \
  -d '{"title":"New Movie","release_year":2020,"genre_id":1}'
```

Use the returned ID for later requests. A successful POST returns 201 and a `Location` header. GET and PUT return 200. Delete returns 200 with `{"message":"Record deleted."}`.

Validation rules:

- Genre name: nonempty string, at most 80 bytes, unique.
- Movie title: nonempty string, at most 150 bytes.
- Release year: JSON integer from 1888 through 2100.
- `genre_id`: JSON integer referring to an existing genre.
- Delete a genre's movies first. A genre still in use returns 409.

Errors use JSON: 400 for malformed JSON, 401 for a missing or invalid delete token, 404 for missing routes or records, 405 for an unsupported method, 409 for a constraint conflict, 415 for the wrong content type, and 422 for invalid fields. A missing server token returns 503 on delete routes. Database connection failures return 500 without exposing credentials.

## Run the tests

Keep the server running. In a second terminal, go to the folder containing this README and set `API_TOKEN` to the **same value** used by the server. In the original terminal, before starting the server, you can copy the value for local testing using `printf '%s\n' "$API_TOKEN"`. Do not commit it or include it in screenshots.

```bash
read -rsp 'Server API token: ' API_TOKEN; echo
export API_TOKEN
bash code/test_curl.sh
```

Expected result: **29 checks passed**. The suite creates its own genre and movie and deletes them when it finishes successfully. It leaves the seed records in place. A failed cURL run stops immediately; its last created test IDs are shown in the output so they can be removed after the problem is fixed.

For browser tests, open <http://localhost:8080/test_client.html>, enter the same token, and click **Run all tests**. Expected result: **23 checks passed**. The page also has individual buttons for all 10 endpoints. Leave the token blank or enter a wrong token to test rejected deletes manually.

## Run through NGINX on Ubuntu

The supplied example uses PHP 8.3 on Ubuntu 24.04. Adjust the FPM service and socket names if your PHP version differs.

```bash
sudo mkdir -p /var/www/movie-api/code
sudo cp code/* /var/www/movie-api/code/
sudo cp code/nginx.conf.example /etc/nginx/sites-available/movie-api
sudo ln -s /etc/nginx/sites-available/movie-api /etc/nginx/sites-enabled/movie-api
sudoedit /etc/php/8.3/fpm/pool.d/www.conf
```

Inside the existing `[www]` pool, add the values below. Replace the password and token with the local values you chose earlier. Keep this configuration outside the repository.

```ini
env[DB_HOST] = 127.0.0.1
env[DB_PORT] = 3306
env[DB_USER] = movie_app
env[DB_PASSWORD] = YOUR_LOCAL_PASSWORD
env[API_TOKEN] = YOUR_LOCAL_TOKEN
```

Then start the services and check the response:

```bash
sudo php-fpm8.3 -t
sudo nginx -t
sudo systemctl restart php8.3-fpm nginx
curl -i http://localhost:8081/movies
BASE_URL=http://localhost:8081 bash code/test_curl.sh
```

Open <http://localhost:8081/test_client.html> for the same browser tests through NGINX. NGINX passes API requests to `index.php` through PHP-FPM and forwards the Authorization header. The static allowlist serves only `test_client.html`; requests for the SQL schema or source files go to the API and get a JSON 404.

This is a local deployment, bound to loopback. It is not a public hosted website. Read, create, and update routes are public within this local server, as listed above. Only delete routes require the token. Do not expose this classroom demo on the internet without additional access controls and HTTPS.

To stop the Ubuntu deployment:

```bash
sudo systemctl stop nginx php8.3-fpm
```

## Slides

`presentation/api_tutorial.md` explains every endpoint with requests and responses. `presentation/nginx_deployment.md` covers setup and shows real test captures. Both PDFs are included. To export again with Marp CLI:

```bash
npx @marp-team/marp-cli presentation/api_tutorial.md --pdf --allow-local-files
npx @marp-team/marp-cli presentation/nginx_deployment.md --pdf --allow-local-files
```

## Verification and submission

See `presentation/verification.md` for the recorded environment and test results. Personal declarations about authorship and understanding in the rubric need Basil's own review. Assistance was used to implement, test, and document this version. This README does not claim that Basil independently wrote or can explain every line.

Repository: <https://github.com/basilwaz/h>

Canvas requires one ZIP containing the `project1/` folder. Review the personal checklist, then upload `ASE-230-Project1-BW.zip` to the Project 1 assignment. A GitHub push does not count as the Canvas upload.

## Sources

- Supplied `project1_overview(1).md` and `project1_rubric(1).md`, sections Required Work, Submission Structure, and Final Submission Checklist. Used for project requirements.
- Supplied `project1_rubric_example.md`, used for organization only. Its student/course topic and example scores were not reused.
- Supplied `plan (1).md`, retained as the earlier HW2 plan. Topic revised with Basil's approval.
- PHP Manual, “PDO::prepare.” <https://www.php.net/manual/en/pdo.prepare.php>
- PHP Manual, “hash_equals.” <https://www.php.net/manual/en/function.hash-equals.php>
- NGINX, “Module ngx_http_fastcgi_module.” <https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html>
- MySQL 8.0 Reference Manual, “FOREIGN KEY Constraints.” <https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html>
- Marp, “Markdown Presentation Ecosystem.” <https://marp.app/>

No lecture page numbers are claimed because specific lecture pages were not used as sources.
