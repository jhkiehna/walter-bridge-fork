<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnqueueJobs extends Command
{
    protected $signature = 'enqueue-jobs {recordType : accepted values: interviews | sendouts | coded | calls | emails}';
    protected $description = 'Enqueues records to be published to kafka';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch (strtolower($this->argument('recordType'))) {
            case 'interviews':
                $model = 'App\Interview';
                break;
            case 'sendouts':
                $model = 'App\Sendout';
                break;
            case 'coded':
                $model = 'App\CandidateCoded';
                break;
            case 'calls':
                $model = 'App\Call';
                break;
            case 'emails':
                $model = 'App\Email';
                break;
            default:
                $this->error("\n Unknown argument: {$this->argument('recordType')}\n");
                $this->info("\n Acceptable arguments: interviews, sendouts, coded, calls, emails\n");
                $this->info("\n Aborting...\n");
                return;
        }

        $progressBar = $this->output->createProgressBar($model::count());
        $progressBar->setFormat(" %message% \n\n %current%/%max% [%bar%] %percent:3s%% \n\n Elapsed / Estimated: \t %elapsed:6s% / %estimated:-6s% \n Memory Used: %memory:6s%\n");
        $progressBar->setMessage("Enqueueing {$model}s...");
        $progressBar->start();

        $model::chunk(500, function ($dataChunk) use ($progressBar) {
            if (!empty($dataChunk)) {
                $dataChunk->each(function ($record) use ($progressBar) {
                    $record->publishToKafka();

                    $progressBar->advance();
                });
            }
        });
    }
}
