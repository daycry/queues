<?php

namespace Daycry\Queues\Jobs;

use Daycry\Queues\Interfaces\JobInterface;

class Command implements JobInterface
{
    protected object $params;

    public function __construct(object $params)
    {
       $this->params = $params;
    }

    public function execute()
    {
        return command( $this->params->command );
    }
}