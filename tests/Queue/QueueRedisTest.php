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

use Config\Services;
use DateInterval;
use DateTime;
use Daycry\Queues\Job;
use Tests\Support\TestCase;

/**
 * @internal
 */
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

    public function testWorkerCommand(): void
    {
        $this->injectMockQueueWorker('redis');

        $command = 'job:test';

        $job = new Job();
        $job->command($command);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);

        command('queues:worker default --oneTime');

        $this->assertSame('Commands can output text. []', Services::response()->getBody());
    }

    public function testWorkerClasses(): void
    {
        $this->injectMockQueueWorker('redis');

        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);

        command('queues:worker default --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertSame('Hi Contructor method executed with this params:{"param1":1,"param2":2}', Services::response()->getBody()->json->data);
    }

    public function testWorkerShell(): void
    {
        $this->injectMockQueueWorker('redis');

        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);

        command('queues:worker default --oneTime');

        $this->assertContains('src', Services::response()->getBody());
    }

    public function testWorkerEvent(): void
    {
        $this->injectMockQueueWorker('redis');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);

        command('queues:worker default --oneTime');

        $this->assertTrue(Services::response()->getBody());
    }

    public function testWorkerUrl(): void
    {
        $this->injectMockQueueWorker('redis');

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

        $this->assertIsInt($result);

        command('queues:worker default --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertSame('p1', Services::response()->getBody()->json->param1);
    }

    public function testScheduledShell(): void
    {
        $this->injectMockQueueWorker('redis');

        $command = 'ls';

        $dateTimeObj = new DateTime('now');
        $dateTimeObj->add(new DateInterval('PT1H'));

        $job = new Job();
        $job->shell($command);
        $job->scheduled($dateTimeObj);
        $result = $job->enqueue('default');

        $this->assertIsInt($result);
    }
}
