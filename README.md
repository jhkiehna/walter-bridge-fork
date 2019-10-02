# Walter Bridge

## Deploy Instructions

-   Pull repo
-   ensure correct php pdo drivers are installed for ms sql. odbc or libdb.
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars
-   Add Sentry environment variable `SENTRY_LARAVEL_DSN=`
-   Set redis environment variables
    ```
      QUEUE_CONNECTION=redis
      REDIS_HOST=
      REDIS_PASSWORD=
      REDIS_PORT=
      RED_PREFIX=
    ```
-   Set up systemd services for `php artisan kafka:consume` and `php artisan queue:work`

## Running Locally

-   Pull repo
-   ensure correct php pdo drivers are installed for ms sql. odbc or libdb.
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars
-   Run `touch database/testing.sqlite && touch database/testing-stats.sqlite && touch database/walter-testing.sqlite`

## Running Tests

-   `./vendor/bin/phpunit`
