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

namespace Tests\Support;

use CodeIgniter\Settings\Config\Settings as ConfigSettings;
use CodeIgniter\Settings\Settings;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        // Use Array Settings Handler
        $configSettings           = config(ConfigSettings::class);
        $configSettings->handlers = ['array'];
        $settings                 = new Settings($configSettings);
        Services::injectMock('settings', $settings);
    }

    protected function injectMockQueueWorker(string $worker): void
    {
        service('settings')->set('Queue.worker', $worker);
    }
}
