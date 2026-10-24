# Project 1 verification

Checked October 23, 2026, America/New_York.

## Environment

- PHP 8.3.6 with PDO and pdo_mysql.
- MySQL 8.0.46.
- NGINX 1.24.0 with PHP-FPM 8.3.6.
- Chromium 153 for browser tests.
- Marp CLI 4.5.1 for PDF export.

The same code also passed using MariaDB 10.11.14 before the MySQL run.

## Results

| Check | PHP server, port 8080 | NGINX and FPM, port 8081 |
|---|---|---|
| cURL suite | 29 checks passed | 29 checks passed |
| Browser suite | 23 checks passed | 23 checks passed |
| Create, read, update, delete | Passed for both resources | Passed for both resources |
| Missing and invalid delete tokens | 401 for both routes | 401 for both routes |
| Delete a genre still in use | 409 | 409 |
| Invalid input and missing records | Expected errors | Expected errors |

PHP syntax checks passed for all four PHP files. Updates were read back, successful deletes were followed by 404 reads, and rejected deletes left their records in place. Both test suites created temporary records and deleted them. The two seed movies and two seed genres remained afterward.

NGINX response captured:

```text
HTTP/1.1 200 OK
Server: nginx/1.24.0 (Ubuntu)
Content-Type: application/json; charset=utf-8
X-Powered-By: PHP/8.3.6
```

```json
[{"id":1,"title":"Interstellar","release_year":2014,"genre_id":1},
 {"id":2,"title":"Spirited Away","release_year":2001,"genre_id":2}]
```

`nginx_screenshot.png` shows a real GET /movies response in the browser client through NGINX. `nginx_tests.png` shows the browser test results through NGINX. Credentials are not included.

## Deployment details

Testing used local processes in the build environment. PHP-FPM listened on TCP port 9000 for this run. The database and NGINX were bound to loopback. Runtime files and database storage were outside the submission folder.

The submitted Ubuntu config uses the usual PHP-FPM Unix socket and `/var/www/movie-api/code` location instead. These are environment-specific paths; the API routing and Authorization forwarding are the same. The README explains how to set the local password and token before starting it. The temporary test services were stopped after verification. There is no public hosted URL.

## Remaining personal checks

Basil must review the code and the personal declarations in the rubric before submitting. The source files alone cannot establish his understanding or independent authorship. No earlier completion dates or Canvas upload receipt have been invented.
