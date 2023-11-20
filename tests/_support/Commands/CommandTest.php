<?php

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;

/**
 * @internal
 */
final class CommandTest extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'job:test';
    protected $description = 'Tests Jobs';
    protected $usage       = 'job:test';

    public function run(array $params = [])
    {
        echo 'Commands can output text.';
    }
}
