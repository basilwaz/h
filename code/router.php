<?php
// Give the development server one public file; route everything else to the API.
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/test_client.html') {
    return false;
}
require __DIR__ . '/index.php';
