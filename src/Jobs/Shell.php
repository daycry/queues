<?php

namespace Daycry\Queues\Jobs;

use Daycry\Queues\Interfaces\JobInterface;

class Shell implements JobInterface
{
    protected object $params;

    public function __construct(object $params)
    {
       $this->params = $params;
    }

    public function execute()
    {
        exec($this->params->command, $output);

        return $output;
    }
}