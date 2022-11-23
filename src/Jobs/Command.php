<?php

namespace Daycry\Queues\Jobs;

class Command
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