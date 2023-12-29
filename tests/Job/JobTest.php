<?php

declare(strict_types=1);

namespace Tests\Job;

use DateTime;
use Daycry\Queues\Exceptions\JobException;
use Daycry\Queues\Job;
use stdClass;
use Tests\Support\TestCase;

final class JobTest extends TestCase
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
        $name = 'job:test';
        $command = json_decode(json_encode(['command' => $name, 'options' => ['param' => 1]]));

        $callback = new stdClass();
        $callback->url = 'https://httpbin.org/post';
        $callback->options = json_decode(json_encode(['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]));

        $objectExpected = new stdClass();
        $objectExpected->type = 'command';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;
        $objectExpected->callback = $callback;
        $objectExpected->attempts = 0;
        $objectExpected->queue = null;

        $job = new Job();
        $job->command($name, ['param' => 1]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' =>['X-API-KEY' => '1234']]);

        $this->assertSame('command', $job->getType());
        $this->assertEquals($command, $job->getAction());
        $this->assertEquals($objectExpected, $job->toObject());
    }

    public function testInvalidMethod()
    {
        $this->expectException(JobException::class);
        
        $name = 'job:test';
        $command = json_decode(json_encode(['command' => $name, 'options' => []]));

        $objectExpected = new stdClass();
        $objectExpected->type = 'command';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;
        $objectExpected->callback = null;

        $job = new Job();
        $job->command($name);
        $job->setFoo('foo');
    }

    public function testCommandException()
    {
        $this->expectException(JobException::class);
        $name = 'job:test';
        $command = json_decode(json_encode(['command' => $name, 'options' => []]));

        $objectExpected = new stdClass();
        $objectExpected->type = 'foo';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;

        $job = new Job($objectExpected);
    }

    public function testShell()
    {
        $name = 'job:test';
        $command = json_decode(json_encode(['command' => $name, 'options' => []]));

        $objectExpected = new stdClass();
        $objectExpected->type = 'shell';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;
        $objectExpected->callback = null;
        $objectExpected->attempts = 0;
        $objectExpected->queue = null;

        $job = new Job();
        $job->shell($name);

        $this->assertSame('shell', $job->getType());
        $this->assertEquals($command, $job->getAction());
        $this->assertEquals($objectExpected, $job->toObject());
    }

    public function testClasses()
    {
        $command = json_decode(json_encode(['class' => \Tests\Support\Classes::class, 'method' => 'run', 'options' => ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]]));

        $objectExpected = new stdClass();
        $objectExpected->type = 'classes';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;
        $objectExpected->callback = null;
        $objectExpected->attempts = 0;
        $objectExpected->queue = null;

        $job = new Job();
        $job->classes(\Tests\Support\Classes::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);

        $this->assertSame('classes', $job->getType());
        $this->assertEquals($command, $job->getAction());
        $this->assertEquals($objectExpected, $job->toObject());
    }

    public function testEvent()
    {
        $name = 'email';
        $command = json_decode(json_encode(['event' => $name, 'options' => []]));

        $objectExpected = new stdClass();
        $objectExpected->type = 'event';
        $objectExpected->action = $command;
        $objectExpected->schedule = null;
        $objectExpected->callback = null;
        $objectExpected->attempts = 0;
        $objectExpected->queue = null;

        $job = new Job();
        $job->event($name);

        $this->assertSame('event', $job->getType());
        $this->assertEquals($command, $job->getAction());
        $this->assertEquals($objectExpected, $job->toObject());
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

        $objectExpected = new stdClass();
        $objectExpected->type = 'url';
        $objectExpected->action = json_decode(json_encode(array_merge(['url' => $url], $options)));
        $objectExpected->schedule = null;
        $objectExpected->callback = null;
        $objectExpected->attempts = 0;
        $objectExpected->queue = null;

        $job = new Job();
        $job->url($url, $options);

        $this->assertSame('url', $job->getType());
        $this->assertEquals(json_decode(json_encode(array_merge(['url' => $url], $options))), $job->getAction());
        $this->assertEquals($objectExpected, $job->toObject());
    }

    public function testCommandScheduled()
    {
        $name = 'job:test';
        $command = json_decode(json_encode(['command' => $name, 'options' => []]));
        $scheduled = new DateTime('now');

        $objectExpected = new stdClass();
        $objectExpected->type = 'command';
        $objectExpected->action = $command;
        $objectExpected->schedule = $scheduled;
        $objectExpected->callback = null;
        $objectExpected->attempts = 0;
        $objectExpected->queue = null;

        $job = new Job();
        $job->command($name);
        $job->scheduled($scheduled);

        $this->assertSame('command', $job->getType());
        $this->assertEquals($command, $job->getAction());
        $this->assertEquals(json_decode(json_encode($objectExpected)), $job->toObject());
    }
}