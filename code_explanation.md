# Code walkthrough

This is a short guide for reviewing the project before presenting it.

## Files

- `schema.sql` creates the database, two tables, and sample records.
- `db.php` reads local environment settings and opens a PDO connection.
- `auth.php` checks the header for the two delete routes.
- `index.php` chooses the route, validates data, runs SQL, and returns JSON.
- `router.php` lets PHP's development server serve the test page and API.
- `test_curl.sh` sends requests from the terminal and checks their statuses.
- `test_client.html` sends requests with fetch and displays JSON in the browser.

## What happens during a request

For `PUT /movies/1`, PHP reads the method and path. The route check allows only `movies` or `genres` and a positive ID. PHP checks that movie 1 exists, reads the JSON body, and validates all three fields. It also checks that the genre exists. A prepared UPDATE changes the movie, then a SELECT reads the saved record for the response.

For `DELETE /movies/1`, the token is checked first. A missing or incorrect token gets 401. A valid token lets the route look up the movie and delete it. A later GET gets 404 because that row no longer exists.

## Why these choices are here

- Prepared statements keep incoming values separate from SQL instructions.
- Table names come only from an allowlist because placeholders cannot stand in for SQL table names.
- A foreign key stops movies from pointing to missing genres.
- The genre delete restriction avoids leaving movies with broken links.
- The password and token come from the environment so real secrets stay out of GitHub.
- The browser uses textContent for results so stored text is displayed as text.
- PUT expects the complete record. PATCH is not implemented and returns 405.

## Practice questions

1. Can you add a genre, use its returned ID to add a movie, and explain both SQL statements?
2. Can you explain why a wrong token returns 401 before deleting anything?
3. Can you explain why deleting a used genre returns 409?
4. Can you change a movie title and show that it stays changed on a later GET?
5. Can you explain the difference between the PHP development server and NGINX with PHP-FPM?

Read the actual source while working through these questions. This guide does not certify understanding.
