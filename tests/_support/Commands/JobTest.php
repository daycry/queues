<?php

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;

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
        echo true;
    }
}