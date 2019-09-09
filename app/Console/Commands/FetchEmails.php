<?php

namespace App\Console\Commands;

use App\Console\Commands\BaseFetch;
use App\Services\Walter\EmailReader;

class FetchEmails extends BaseFetch
{
    protected $signature = 'fetch:emails {--between : Be prompted for Start and End dates}';
    protected $description = 'fetch all emails. Or use --between option to specify Start/End Dates.';

    public function __construct(EmailReader $reader)
    {
        parent::__construct();

        $this->reader = $reader;
        $this->recordType = 'Email';
    }
}
