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

namespace Tests\Queue;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Response;
use DateTime;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Job;
use stdClass;
use Tests\Support\TestCase;

/**
 * @internal
 */
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

    public function testCommand(): void
    {
        $command = 'job:test';

        $job = new Job();
        $job->command($command, ['param' => 1]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertSame($result, 'Commands can output text. {"param":"1"}');
    }

    public function testClasses(): void
    {
        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertSame($result, 'Hi Contructor method executed with this params:{"param1":1,"param2":2}');
    }

    public function testWorkerException(): void
    {
        $this->injectMockQueueWorker('foo');
        $this->expectException(QueueException::class);
        $command = 'job:test';

        $objectExpected           = new stdClass();
        $objectExpected->type     = 'command';
        $objectExpected->action   = $command;
        $objectExpected->schedule = null;

        $job    = new Job($objectExpected);
        $result = $job->enqueue('default');
    }

    public function testQueueException(): void
    {
        $this->expectException(QueueException::class);
        $command = 'job:test';

        $objectExpected           = new stdClass();
        $objectExpected->type     = 'command';
        $objectExpected->action   = $command;
        $objectExpected->schedule = null;

        $job    = new Job($objectExpected);
        $result = $job->enqueue('foo');
    }

    public function testShell(): void
    {
        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertTrue(in_array('src', $result, true));
    }

    public function testShellScheduled(): void
    {
        $command = 'ls';

        $job = new Job();
        $job->scheduled(new DateTime('now'));
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertTrue(in_array('src', $result, true));
    }

    public function testEvent(): void
    {
        Events::on('custom', static fn () => 'Hello event');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('default');

        $this->assertTrue($result);
    }

    public function testUrl(): void
    {
        $url = 'https://httpbin.org/post';

        $options = [
            'verify'   => false,
            'method'   => 'post',
            'body'     => ['param1' => 'p1'],
            'dataType' => 'json',
            'headers'  => [
                'X-API-KEY' => '1234',
            ],
        ];

        $job = new Job();
        $job->url($url, $options);
        $result = $job->enqueue('default');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertTrue(property_exists($result->getBody(), 'headers'));
        $this->assertTrue(property_exists($result->getBody()->headers, 'X-Api-Key'));
    }
}
