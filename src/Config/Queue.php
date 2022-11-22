<?php

namespace Daycry\Queues\Config;

use CodeIgniter\Config\BaseConfig;

class Queue extends BaseConfig
{
    /**
     * Host
     */
    public $host = 'localhost';

    /**
     * Port
     */
    public $port = 11300;

    /**
     * Queues
     * Example: $queues = ['name1','name2']; or $queues = 'name1,name2';
     */
    public $queues = 'default';
}