<?php

declare(strict_types=1);

namespace Tests\Queue;

use DateInterval;
use DateTime;
use Config\Services;
use Daycry\Queues\Job;
use Tests\Support\TestCase;

final class QueueRedisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testCommand()
    {
        $this->injectMockQueueWorker('redis');

        $command = 'job:test';

        $job = new Job();
        $job->command($command);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }

    public function testWorkerCommand()
    {
        $this->injectMockQueueWorker('redis');

        command('queues:worker default --oneTime');

        $this->assertEquals('Commands can output text. []', Services::response()->getBody());
    }

    public function testClasses()
    {
        $this->injectMockQueueWorker('redis');

        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }

    public function testWorkerClasses()
    {
        $this->injectMockQueueWorker('redis');

        command('queues:worker default --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertEquals('Hi Contructor method executed with this params:{"param1":1,"param2":2}', Services::response()->getBody()->json->data);
    }

    public function testShell()
    {
        $this->injectMockQueueWorker('redis');

        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }

    public function testWorkerShell()
    {
        $this->injectMockQueueWorker('redis');

        command('queues:worker default --oneTime');

        $this->assertContains('src', Services::response()->getBody());
    }

    public function testEvent()
    {
        $this->injectMockQueueWorker('redis');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }

    public function testWorkerEvent()
    {
        $this->injectMockQueueWorker('redis');

        command('queues:worker default --oneTime');

        $this->assertTrue(Services::response()->getBody());
    }

    public function testUrl()
    {
        $this->injectMockQueueWorker('redis');

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
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }

    public function testWorkerUrl()
    {
        $this->injectMockQueueWorker('redis');

        command('queues:worker default --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertEquals('p1', Services::response()->getBody()->json->param1);
    }

    public function testScheduledShell()
    {
        $this->injectMockQueueWorker('redis');

        $command = 'ls';

        $dateTimeObj = new DateTime('now');
        $dateTimeObj->add(new DateInterval("PT1H"));

        $job = new Job();
        $job->shell($command);
        $job->scheduled($dateTimeObj);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }
}
