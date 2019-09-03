<?php

namespace App\Console\Commands;

use App\Services\Stats\CallReader;
use App\Console\Commands\BaseFetch;

class FetchCalls extends BaseFetch
{
    protected $signature = 'fetch:calls {--between : Be prompted for Start and End dates}';
    protected $description = 'fetch all calls. Or use --between option to specify Start/End Dates.';

    public function __construct(CallReader $reader)
    {
        parent::__construct();

        $this->reader = $reader;
        $this->recordType = 'Call';
    }
}
