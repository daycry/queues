<?php

namespace Daycry\Queues\Jobs;

class Shell
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