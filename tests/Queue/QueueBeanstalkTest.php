<?php

declare(strict_types=1);

namespace Tests\Queue;

use CodeIgniter\HTTP\Response;
use Config\Services;
use DateInterval;
use DateTime;
use Daycry\Queues\Job;
use Pheanstalk\Values\JobId;
use Tests\Support\TestCase;

final class QueueBeanstalkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testClasses()
    {
        $this->injectMockQueueWorker('beanstalk');

        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertEquals('string', gettype($result));
    }

    public function testWorkerClasses()
    {
        $this->injectMockQueueWorker('beanstalk');

        command('queues:worker default --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertEquals('Hi Contructor method executed with this params:{"param1":1,"param2":2}', Services::response()->getBody()->json->data);
    }

    public function testCommand()
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'job:test';

        $job = new Job();
        $job->command($command);
        $job->setPriority(10)->setTtr(3600);
        $result = $job->enqueue('default');

        $this->assertEquals('string', gettype($result));
    }

    public function testWorkerCommand()
    {
        $this->injectMockQueueWorker('beanstalk');

        command('queues:worker default --oneTime');

        $this->assertEquals('Commands can output text. []', Services::response()->getBody());
    }

    public function testShell()
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertEquals('string', gettype($result));
    }

    public function testWorkerShell()
    {
        $this->injectMockQueueWorker('beanstalk');

        command('queues:worker default --oneTime');

        $this->assertContains('src', Services::response()->getBody());
    }

    public function testEvent()
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('default');

        $this->assertEquals('string', gettype($result));
    }

    public function testWorkerEvent()
    {
        $this->injectMockQueueWorker('beanstalk');

        command('queues:worker default --oneTime');

        $this->assertTrue(Services::response()->getBody());
    }

    public function testUrl()
    {
        $this->injectMockQueueWorker('beanstalk');

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

        $this->assertEquals('string', gettype($result));
    }

    public function testWorkerUrl()
    {
        $this->injectMockQueueWorker('beanstalk');

        command('queues:worker default --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertEquals('p1', Services::response()->getBody()->json->param1);
    }

    public function testScheduledShell()
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'pwd';

        $dateTimeObj = new DateTime('now');
        $dateTimeObj->add(new DateInterval("PT2H"));

        $job = new Job();
        $job->shell($command);
        $job->scheduled($dateTimeObj);
        $job->setToDefaultQueue();
        $result = $job->enqueue();

        $this->assertEquals('string', gettype($result));
    }
}
