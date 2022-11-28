<?php

namespace Daycry\Queues\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Daycry\Queues\Config\Queue;

class QueueRun extends BaseCommand
{
    protected $group       = 'Queues';
    protected $name        = 'queue:run';
    protected $description = 'Start queue worker.';

    public function run(array $params)
    {
        $config = new Queue();
        return (new $config->worker($config))->watch();
        CLI::write('Started successfully.', 'green');
    }
}