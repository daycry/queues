<?php

declare(strict_types=1);

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

    protected function injectMockQueueWorker(string $worker)
    {
        service('settings')->set('Queue.worker', $worker);
    }
}
