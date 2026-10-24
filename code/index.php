<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function respond($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

function read_body(): array {
    $type = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($type !== 'application/json') {
        respond(['error' => 'Use Content-Type: application/json.'], 415);
    }
    $body = json_decode(file_get_contents('php://input'));
    if (json_last_error() !== JSON_ERROR_NONE || !is_object($body)) {
        respond(['error' => 'Send a valid JSON object.'], 400);
    }
    return (array) $body;
}

function validate_body(array $body, string $resource): array {
    if ($resource === 'genres') {
        if (!isset($body['name']) || !is_string($body['name']) ||
            trim($body['name']) === '' || strlen(trim($body['name'])) > 80) {
            respond(['error' => 'name must be 1 to 80 bytes.'], 422);
        }
        return ['name' => trim($body['name'])];
    }
    if (!isset($body['title']) || !is_string($body['title']) ||
        trim($body['title']) === '' || strlen(trim($body['title'])) > 150) {
        respond(['error' => 'title must be 1 to 150 bytes.'], 422);
    }
    if (!isset($body['release_year']) || !is_int($body['release_year']) ||
        $body['release_year'] < 1888 || $body['release_year'] > 2100) {
        respond(['error' => 'release_year must be an integer from 1888 to 2100.'], 422);
    }
    if (!isset($body['genre_id']) || !is_int($body['genre_id']) || $body['genre_id'] < 1) {
        respond(['error' => 'genre_id must be a positive integer.'], 422);
    }
    return ['title' => trim($body['title']), 'release_year' => $body['release_year'],
            'genre_id' => $body['genre_id']];
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!preg_match('#^/(movies|genres)(?:/([1-9][0-9]*))?/?$#', $path, $match)) {
    respond(['error' => 'Route not found.'], 404);
}
$resource = $match[1]; // Only these two table names can enter the SQL below.
$id = isset($match[2]) ? filter_var($match[2], FILTER_VALIDATE_INT) : null;
if ($id === false) {
    respond(['error' => 'Invalid ID.'], 404);
}
$method = $_SERVER['REQUEST_METHOD'];
$allowed = $id === null ? ['GET', 'POST'] : ['GET', 'PUT', 'DELETE'];
if (!in_array($method, $allowed, true)) {
    header('Allow: ' . implode(', ', $allowed));
    respond(['error' => 'Method not allowed for this route.'], 405);
}
if ($method === 'DELETE') {
    check_token();
}

try {
    $db = get_db();
    if ($method === 'GET' && $id === null) {
        respond($db->query("SELECT * FROM $resource ORDER BY id")->fetchAll());
    }
    if ($id !== null) {
        $query = $db->prepare("SELECT * FROM $resource WHERE id = ?");
        $query->execute([$id]);
        $record = $query->fetch();
        if (!$record) {
            respond(['error' => 'Record not found.'], 404);
        }
        if ($method === 'GET') {
            respond($record);
        }
    }
    if ($method === 'DELETE') {
        $query = $db->prepare("DELETE FROM $resource WHERE id = ?");
        $query->execute([$id]);
        respond(['message' => 'Record deleted.']);
    }
    $data = validate_body(read_body(), $resource);
    if ($resource === 'movies') {
        $query = $db->prepare('SELECT id FROM genres WHERE id = ?');
        $query->execute([$data['genre_id']]);
        if (!$query->fetch()) {
            respond(['error' => 'genre_id must refer to an existing genre.'], 422);
        }
    }
    if ($method === 'POST') {
        $sql = $resource === 'genres'
            ? 'INSERT INTO genres (name) VALUES (?)'
            : 'INSERT INTO movies (title, release_year, genre_id) VALUES (?, ?, ?)';
        $query = $db->prepare($sql);
        $query->execute(array_values($data));
        $id = (int) $db->lastInsertId();
        header("Location: /$resource/$id");
    } else {
        $sql = $resource === 'genres'
            ? 'UPDATE genres SET name = ? WHERE id = ?'
            : 'UPDATE movies SET title = ?, release_year = ?, genre_id = ? WHERE id = ?';
        $query = $db->prepare($sql);
        $query->execute([...array_values($data), $id]);
    }
    $query = $db->prepare("SELECT * FROM $resource WHERE id = ?");
    $query->execute([$id]);
    respond($query->fetch(), $method === 'POST' ? 201 : 200);
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        respond(['error' => 'Duplicate genre name or genre still used by a movie.'], 409);
    }
    error_log($error->getMessage());
    respond(['error' => 'Database unavailable. Check server settings.'], 500);
}
