<?php

namespace App\Console\Commands;

use App\Console\Commands\BaseFetch;
use App\Services\Walter\SendoutReader;

class FetchSendouts extends BaseFetch
{
    protected $signature = 'fetch:sendouts {--between : Be prompted for Start and End dates}';
    protected $description = 'fetch all sendouts. Or use --between option to specify Start/End Dates.';

    public function __construct(SendoutReader $reader)
    {
        parent::__construct();

        $this->reader = $reader;
        $this->recordType = 'Sendout';
    }
}
