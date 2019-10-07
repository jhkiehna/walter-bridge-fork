<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Services\Stats\CallReader;

class FetchTransferCalls extends Command
{
    protected $signature = 'fetch:transfer-calls';
    protected $description = 'Fetches only transfer calls from stats database calls table';

    protected $reader;

    protected $startDate;
    protected $endDate;

    public function __construct(CallReader $reader)
    {
        parent::__construct();

        $this->reader = $reader;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->validateBetweenOption()) {
            return;
        }

        $query = $this->reader->getTransferOnlyQuery($this->startDate, $this->endDate);

        if ($query->count() == 0) {
            $this->abortMessage("No records found...");
            return;
        }

        $progressBar = $this->output->createProgressBar($query->count());
        $progressBar->setFormat(" %message% \n\n %current%/%max% [%bar%] %percent:3s%% \n\n Elapsed / Estimated: \t %elapsed:6s% / %estimated:-6s% \n Memory Used: %memory:6s%\n");
        $progressBar->setMessage("Processing transfer calls...");
        $progressBar->start();

        $query->orderBy($this->reader->primaryKey)->chunk(500, function ($dataChunk) use ($progressBar) {
            if (!empty($dataChunk)) {
                $dataChunk->each(function ($record) use ($progressBar) {
                    // dd($record);

                    $localRecord = $this->reader->localModel::writeWithForeignRecord($record);

                    if ($localRecord) {
                        $localRecord->publishToKafka();
                    }

                    $progressBar->advance();
                });
            }
        });

        $progressBar->finish();

        $this->info("\n Processing Complete \n");
    }

    private function validateBetweenOption()
    {
        try {
            $this->startDate = Carbon::parse($this->ask('Enter a Start Date (format: Y-m-d)'));
            $this->endDate = Carbon::parse($this->ask('Enter an End Date (format: Y-m-d)'));
        } catch (Throwable $e) {
            $this->abortMessage($e->getMessage() . "\n\nError parsing dates. Try again.");
            return false;
        }

        if ($this->startDate->isAfter($this->endDate)) {
            $this->abortMessage('End Date cannot be before Start Date. Try again.');
            return false;
        }

        $this->info('Fetch Transfer Calls between ' .
            $this->startDate->toFormattedDateString() .
            ' and ' . $this->endDate->toFormattedDateString() . " ?\n");

        if (strtolower(substr($this->ask('Is this correct? [y/n]'), 0, 1)) != 'y') {
            $this->abortMessage("You didn't say yes.");
            return false;
        }

        $this->continuingMessage();
        return true;
    }

    private function abortMessage($message)
    {
        $this->error($message);
        $this->info("\nAborting...\n");
    }

    private function continuingMessage()
    {
        $this->info("Fetching All Transfer Calls" .
            "s between " . $this->startDate->toFormattedDateString() .
            " and " . $this->endDate->toFormattedDateString() . "...\n");
    }
}
