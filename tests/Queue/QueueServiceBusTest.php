<?php

declare(strict_types=1);

namespace Tests\Queue;

use Config\Services;
use DateInterval;
use DateTime;
use Daycry\Queues\Job;
use Tests\Support\TestCase;

final class QueueServiceBusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @vcr testCommandServiceBus.json
     * @group fixtures
     */
    public function testCommand()
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'job:test';

        $job = new Job();
        $job->command($command);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    /**
     * @vcr testWorkerCommandServiceBus.json
     * @group fixtures
     */
    public function testWorkerCommand()
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertEquals('Commands can output text. []', Services::response()->getBody());
    }

    /**
     * @vcr testClassesServiceBus.json
     * @group fixtures
     */
    public function testClasses()
    {
        $this->injectMockQueueWorker('serviceBus');

        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    /**
     * @vcr testWorkerClassesServiceBus.json
     * @group fixtures
     */
    public function testWorkerClasses()
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertEquals('Hi Contructor method executed with this params:{"param1":1,"param2":2}', Services::response()->getBody()->json->data);
    }

    /**
     * @vcr testShellServiceBus.json
     * @group fixtures
     */
    public function testShell()
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $job->setLabel('test-shell');
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    /**
     * @vcr testWorkerShellServiceBus.json
     * @group fixtures
     */
    public function testWorkerShell()
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertContains('src', Services::response()->getBody());
    }

    /**
     * @vcr testEventServiceBus.json
     * @group fixtures
     */
    public function testEvent()
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    /**
     * @vcr testWorkerEventServiceBus.json
     * @group fixtures
     */
    public function testWorkerEvent()
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertTrue(Services::response()->getBody());
    }

    /**
     * @vcr testUrlServiceBus.json
     * @group fixtures
     */
    public function testUrl()
    {
        $this->injectMockQueueWorker('serviceBus');

        $url = 'https://httpbin.org/post';

        $options = [
            'verify' => false,
            'method' => 'post',
            'body' => ['param1' => 'p1'],
            'dataType' => 'json',
            'headers' => [
                'X-API-KEY' => '1234'
            ]
        ];

        $job = new Job();
        $job->url($url, $options);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    /**
     * @vcr testWorkerUrlServiceBus.json
     * @group fixtures
     */
    public function testWorkerUrl()
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertEquals('p1', Services::response()->getBody()->json->param1);
    }

    /**
     * @vcr testScheduledShellServiceBus.json
     * @group fixtures
     */
    /*public function testScheduledShell()
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'ls';

        $dateTimeObj= (new DateTime())->setTimestamp((int)getenv('MOCK_TIME'));
        $dateTimeObj->add(new DateInterval("PT1H"));

        $job = new Job();
        $job->shell($command);
        $job->scheduled($dateTimeObj);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }*/
}
