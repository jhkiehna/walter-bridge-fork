<?php

namespace App\Console\Commands;

use App\Console\Commands\BaseFetch;
use App\Services\Walter\InterviewReader;

class FetchInterviews extends BaseFetch
{
    protected $signature = 'fetch:interviews {--between : Be prompted for Start and End dates}';
    protected $description = 'fetch all interviews. Or use --between option to specify Start/End Dates.';

    public function __construct(InterviewReader $reader)
    {
        parent::__construct();

        $this->reader = $reader;
        $this->recordType = 'Interview';
    }
}
