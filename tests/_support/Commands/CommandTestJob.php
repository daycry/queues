<?php

declare(strict_types=1);

/**
 * This file is part of Daycry Queues.
 *
 * (c) Daycry <daycry9@proton.me>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Commands;

use CodeIgniter\CLI\BaseCommand;

/**
 * @internal
 */
final class CommandTestJob extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'job:test';
    protected $description = 'Tests Jobs';
    protected $usage       = 'job:test';

    public function run(array $params = []): void
    {
        echo 'Commands can output text. ' . json_encode($params);
    }
}
