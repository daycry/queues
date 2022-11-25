<?php

namespace Daycry\Queues\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Daycry\Queues\libraries\Worker;
use Daycry\Queues\Config\Queue;

class QueueRun extends BaseCommand
{
    protected $group       = 'Queues';
    protected $name        = 'queue:run';
    protected $description = 'Start queue worker.';

    public function run(array $params)
    {
        $config = new Queue();
        $status = (new Worker($config))->watch();
        CLI::write('Started successfully.', 'green');
    }
}