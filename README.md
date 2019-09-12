# Walter Bridge

## Deploy Instructions

-   Pull repo
-   ensure correct php pdo drivers are installed for ms sql. odbc or libdb.
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars
-   Ensure variable `QUEUE_CONNECTION=database`
-   Set up supervisor to run the queue workers
    --`sudo apt-get install supervisor`
    --`sudo vim /etc/supervisor/conf.d/walter-bridge-worker.conf`
    --create laravel worker

```
[program:walter-bridge-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work database --sleep=3 --tries=3
autostart=true
autorestart=true
user=forge
numprocs=6
redirect_stderr=true
stdout_logfile=/path/to/app/walter-bridge-worker.log
```

-   Set up supervisor to run a the kafka user consumer
    --`sudo vim /etc/supervisor/conf.d/walter-bridge-kafka-consumer-worker.conf`
    --create laravel worker

```
[program:walter-bridge-kafka-consumer-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan kafka:consume --sleep=3 --tries=3
autostart=true
autorestart=true
user=forge
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/app/walter-bridge-kafka-consumer-worker.log
```

-   Update and Start Supervisor
    --`sudo supervisorctl reread`
    --`sudo supervisorctl update`
    --`sudo supervisorctl start walter-bridge-worker:*`
    --`sudo supervisorctl start walter-bridge-kafka-consumer-worker:*`

## Running Locally

-   Pull repo
-   ensure correct php pdo drivers are installed for ms sql. odbc or libdb.
-   `composer install`
-   `cp .env.example .env`
-   Edit environment variables in .env appropriately; fill in DB_WALTER and DB_STATS env vars
-   Run `touch database/testing.sqlite && touch database/testing-stats.sqlite && touch database/walter-testing.sqlite`

## Running Tests

-   `./vendor/bin/phpunit`
