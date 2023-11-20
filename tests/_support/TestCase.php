<?php

declare(strict_types=1);

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use Daycry\Settings\Config\Settings as ConfigSettings;
use Daycry\Settings\Settings;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Daycry\Queues\Config\Queue;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    protected function setUp(): void
    {
        defined('MOCK_TIME') || define('MOCK_TIME', 1_699_981_456);
        defined('MESSAGEID') || define('MESSAGEID', 'MessageId-1234');

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
