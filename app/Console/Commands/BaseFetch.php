<?php

namespace App\Console\Commands;

use Throwable;
use Carbon\Carbon;
use Illuminate\Console\Command;

abstract class BaseFetch extends Command
{
    protected $signature;
    protected $description;

    protected $reader;
    protected $recordType;

    protected $startDate;
    protected $endDate;

    public function handle()
    {
        if (!$this->validateBetweenOption()) {
            return;
        }

        $query = $this->reader->getQuery($this->startDate, $this->endDate);

        if ($query->count() == 0) {
            $this->abortMessage("No records found...");
            return;
        }

        $progressBar = $this->output->createProgressBar($query->count());
        $progressBar->setFormat(" %message% \n\n %current%/%max% [%bar%] %percent:3s%% \n\n Elapsed / Estimated: \t %elapsed:6s% / %estimated:-6s% \n Memory Used: %memory:6s%\n");
        $progressBar->setMessage("Processing {$this->recordType}s...");
        $progressBar->start();

        $query->orderBy($this->reader->primaryKey)->chunk(500, function ($dataChunk) use ($progressBar) {
            if (!empty($dataChunk)) {
                $dataChunk->each(function ($record) use ($progressBar) {
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
        if ($this->option('between')) {
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

            $this->info('Fetch ' . $this->recordType . 's between ' .
                $this->startDate->toFormattedDateString() .
                ' and ' . $this->endDate->toFormattedDateString() . " ?\n");

            if (strtolower(substr($this->ask('Is this correct? [y/n]'), 0, 1)) != 'y') {
                $this->abortMessage("You didn't say yes.");
                return false;
            }

            $this->continuingMessage();
            return true;
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
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $this->info("Fetching All " . $this->recordType .
                "s between " . $this->startDate->toFormattedDateString() .
                " and " . $this->endDate->toFormattedDateString() . "...\n");
            return;
        }

        $this->info("\nNo dates specified.\n");
        $this->info("Fetching All " . $this->recordType . "s...\n");
    }
}
