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

        $dataCollection = $this->fetchRecords();

        $progressBar = $this->output->createProgressBar($dataCollection->count());
        $progressBar->setFormat(" %message% \n %current%/%max% [%bar%] %percent:3s%% \n Time: %elapsed:6s%/%estimated:-6s%  Memory: %memory:6s%");
        $progressBar->setMessage("Processing {$this->recordType}s...");
        $progressBar->start();

        if (!$dataCollection->isEmpty()) {
            $dataCollection->chunk(100)->each(function ($chunk) use ($progressBar) {

                $chunk->each(function ($record) {
                    $this->reader->localModel::writeWithForeignRecord($record);
                });

                $progressBar->advance(100);
            });
        }

        $progressBar->finish();

        $this->info("\n Processing Complete \n");
    }

    private function fetchRecords()
    {
        if (!empty($this->startDate) && !empty($this->endDate)) {
            return $this->reader->getBetween($this->startDate, $this->endDate);
        }

        return $this->reader->getAll();
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
