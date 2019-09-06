<?php

namespace App\Console\Commands;

use App\Console\Commands\BaseFetch;
use App\Services\Walter\CandidateCodedReader;

class FetchCandidatesCoded extends BaseFetch
{
    protected $signature = 'fetch:coded {--between : Be prompted for Start and End dates}';
    protected $description = 'fetch all candidates coded. Or use --between option to specify Start/End Dates.';

    public function __construct(CandidateCodedReader $reader)
    {
        parent::__construct();

        $this->reader = $reader;
        $this->recordType = 'Coded';
    }
}
