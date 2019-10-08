<?php

namespace App\Console\Commands;

use App\Call;
use App\Email;
use App\Sendout;
use App\Interview;
use App\CandidateCoded;
use Illuminate\Console\Command;

class RetryFailed extends Command
{
    protected $signature = 'retry-failed {recordType : accepted values: interviews | sendouts | coded | calls | emails}';
    protected $description = 'Attempt to process any fetched records that ended up in the failed items table';

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
                $query = Interview::has('failedItem')->orderBy('id');
                break;
            case 'sendouts':
                $query = Sendout::has('failedItem')->orderBy('id');
                break;
            case 'coded':
                $query = CandidateCoded::has('failedItem')->orderBy('id');
                break;
            case 'calls':
                $query = Call::has('failedItem')->orderBy('id');
                break;
            case 'emails':
                $query = Email::has('failedItem')->orderBy('id');
                break;
            default:
                $this->error("\n Unknown argument: {$this->argument('recordType')}\n");
                $this->info("\n Acceptable arguments: interviews, sendouts, coded, calls, all\n");
                $this->info("\n Aborting...\n");
                return;
        }

        if ($query->count() == 0) {
            $this->info("\n No failed items for {$this->argument('recordType')} found \n");
            $this->info("\n Aborting...\n");
            return;
        }

        $progressBar = $this->output->createProgressBar($query->count());
        $progressBar->setFormat(" %message% \n\n %current%/%max% [%bar%] %percent:3s%% \n\n Elapsed / Estimated: \t %elapsed:6s% / %estimated:-6s% \n Memory Used: %memory:6s%\n");
        $progressBar->setMessage("Processing failed {$this->argument('recordType')}...");
        $progressBar->start();

        $query->chunk(1000, function ($chunk) use ($progressBar) {
            if (!empty($chunk)) {
                $chunk->each(function ($failedRecord) use ($progressBar) {
                    $oldCentralId = $failedRecord->central_id;
                    $failedRecord->updateCentralId();

                    $failedRecord->refresh();

                    if ($failedRecord->central_id != $oldCentralId) {
                        $failedRecord->publishToKafka();
                        $failedRecord->failedItem->delete();
                    }

                    $progressBar->advance();
                });
            }
        });

        $progressBar->finish();

        $this->info("\n Processing Complete \n");
    }
}
