<?php

namespace Daycry\Queues\Libraries;

use Pheanstalk\Pheanstalk;
use Daycry\Queues\Config\Queue;
use Pheanstalk\Exception;

abstract class Base
{
    protected Queue $config;
    protected $pheanstalk;

    public function __construct(?Queue $config = null)
    {
        $this->config = ($config) ?: new Queue();
        $this->_init_queue();
    }

    private function _init_queue()
    {
        $this->pheanstalk = Pheanstalk::create($this->config->host, $this->config->port);
    }
}