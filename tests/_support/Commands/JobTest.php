<?php

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * @internal
 */
final class JobTest extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'job:test';
    protected $description = 'Job Test';
    protected $usage       = 'job:test';

    public function run(array $params = [])
    {
        CLI::write('Commands can output text.');
        echo true;
    }
}