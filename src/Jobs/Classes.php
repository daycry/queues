<?php

namespace Daycry\Queues\Jobs;

use Daycry\Queues\Interfaces\JobInterface;

class Classes implements JobInterface
{
    protected object $params;

    public function __construct(object $params)
    {
       $this->params = $params;
    }

    public function execute()
    {
        $class = new $this->params->class;
        return $class->{$this->params->method}( $this->params->params );
    }
}