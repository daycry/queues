<?php

declare(strict_types=1);

namespace Tests\Queue;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Response;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Job;
use Tests\Support\TestCase;
use stdClass;

final class QueueSyncTest extends TestCase
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
        $command = 'job:test';

        $job = new Job();
        $job->command($command, ['param' => 1]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' =>['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertEquals($result, 'Commands can output text. {"param":"1"}');
    }

    public function testClasses()
    {
        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' =>['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertEquals($result, 'Hi Contructor method executed with this params:{"param1":1,"param2":2}');
    }

    public function testWorkerException()
    {
        $this->injectMockQueueWorker('foo');
        $this->expectException(QueueException::class);
        $command = 'job:test';

        $objectExpected = new stdClass();
        $objectExpected->type = 'command';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;

        $job = new Job($objectExpected);
        $result = $job->enqueue('default');
    }

    public function testQueueException()
    {
        $this->expectException(QueueException::class);
        $command = 'job:test';

        $objectExpected = new stdClass();
        $objectExpected->type = 'command';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;

        $job = new Job($objectExpected);
        $result = $job->enqueue('foo');
    }

    public function testShell()
    {
        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertTrue(in_array('src', $result));
    }

    public function testEvent()
    {
        Events::on('custom', static function () {
            return "Hello event";
        });

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('default');

        $this->assertEquals('Hello event', $result);
    }

    public function testUrl()
    {
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

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue(property_exists($result->getBody(), 'headers'));
        $this->assertTrue(property_exists($result->getBody()->headers, 'X-Api-Key'));
    }
}