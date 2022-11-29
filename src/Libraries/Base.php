<?php

namespace Daycry\Queues\Libraries;

use Pheanstalk\Pheanstalk;
use Daycry\Queues\Config\Queue;

abstract class Base
{
    protected Queue $config;
    protected $pheanstalk;

    public function __construct(?Queue $config = null)
    {
        $this->config = ($config) ?: config('Queue');
        $this->config->queues   = $this->_parseConfigFile($this->config->queues);
        $this->_init_queue();
    }

    private function _init_queue()
    {
        $this->pheanstalk = Pheanstalk::create($this->config->host, $this->config->port);
    }

    private function _parseConfigFile($attr): array
    {
        if ($attr && ! is_array($attr)) {
            $attr = explode(',', $attr);
        } else {
            $attr = is_array($attr) ? $attr : [];
        }

        return array_map('trim', $attr);
    }
}