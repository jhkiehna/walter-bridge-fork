# Walter Bridge

## Deploy Instructions

-   Pull repo
-   endsure php extensions sybase and odbc are installed
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars

## Running Locally

-   Pull repo
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars
-   Run `touch database/testing.sqlite && touch database/testing-stats.sqlite && touch database/walter-testing.sqlite`

## Running Tests

-   `./vendor/bin/phpunit`
