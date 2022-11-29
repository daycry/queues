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
        $inConstructor = isset($this->params->in_constructor) ? $this->params->in_constructor : false;

        if($inConstructor)
        {
            $class = new $this->params->class($this->params->params);
            return $class->{$this->params->method}();
        }else{
            $class = new $this->params->class;
            return $class->{$this->params->method}( $this->params->params );
        }
    }
}