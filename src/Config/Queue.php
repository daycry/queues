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

    public $jobs = 
    [
        'classes' => \Daycry\Queues\Jobs\Classes::class,
        'command' => \Daycry\Queues\Jobs\Command::class,
        'shell' => \Daycry\Queues\Jobs\Shell::class,
        'url' => \Daycry\Queues\Jobs\Url::class,
        'api' => \Daycry\Queues\Jobs\Api::class
    ];
}