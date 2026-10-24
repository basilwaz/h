<?php
function check_token(): void {
    $expected = getenv('API_TOKEN');
    if (!$expected) {
        respond(['error' => 'API_TOKEN is not configured.'], 503);
    }
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer (\S+)$/i', $header, $match) ||
        !hash_equals($expected, $match[1])) {
        header('WWW-Authenticate: Bearer');
        respond(['error' => 'A valid Bearer token is required.'], 401);
    }
}
