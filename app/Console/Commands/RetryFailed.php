<?php

namespace App\Console\Commands;

use App\Email;
use App\Sendout;
use App\Interview;
use App\FailedItem;
use App\CandidateCoded;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RetryFailed extends Command
{
    protected $signature = 'retry-failed {recordType=all : accepted values: interviews | sendouts | coded | calls | emails}';
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
        $failedItems = new Collection();

        switch (strtolower($this->argument('recordType'))) {
            case 'all':
                $failedItems = FailedItem::all();
                break;
            case 'interviews':
                $failedItems = Interview::failedItems();
                break;
            case 'sendouts':
                $failedItems = Sendout::failedItems();
                break;
            case 'coded':
                $failedItems = CandidateCoded::failedItems();
                break;
            case 'calls':
                $failedItems = Call::failedItems();
                break;
            case 'emails':
                $failedItems = Email::failedItems();
                break;
            default:
                $this->error("\n Unknown argument: {$this->argument('recordType')}\n");
                $this->info("\n Acceptable arguments: interviews, sendouts, coded, calls, all\n");
                $this->info("\n Aborting...\n");
                return;
        }

        if ($failedItems->isEmpty()) {
            $this->info("\n No failed items for {$this->argument('recordType')} found \n");
            $this->info("\n Aborting...\n");
            return;
        }

        $failedItems->each(function ($failedItem) {
            $oldCentralId = $failedItem->failable->central_id;
            $failedItem->failable->updateCentralId();

            if ($this->failable->central_id != $oldCentralId) {
                $failedItem->failable->publishToKafka();
                $failedItem->delete();
            }
        });
    }
}
