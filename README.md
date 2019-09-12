# Walter Bridge

## Deploy Instructions

-   Pull repo
-   ensure correct php pdo drivers are installed for ms sql. odbc or libdb.
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars
-   Ensure variable `QUEUE_CONNECTION=database`
-   Set up supervisor to run a queue worker
    --`sudo apt-get install supervisor`
    --`sudo vim /etc/supervisor/conf.d/laravel-worker.conf`
    --create laravel worker

```
[program:walter-bridge-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/app/worker.log
```

--`sudo supervisorctl reread`
--`sudo supervisorctl update`
--`udo supervisorctl start walter-bridge-worker:*`

## Running Locally

-   Do Deploy instructions
-   Run `touch database/testing.sqlite && touch database/testing-stats.sqlite && touch database/walter-testing.sqlite`

## Running Tests

-   `./vendor/bin/phpunit`
