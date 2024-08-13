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

    public function testWorkerClasses(): void
    {
        $this->injectMockQueueWorker('beanstalk');

        $job = new Job();
        $job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('default');

        $this->assertSame('string', gettype($result));

        command('queues:worker default --oneTime');

        /** @var object $body */
        $body = Services::response()->getBody();

        $this->assertSame('https://httpbin.org/post', $body->url);
        $this->assertSame('Hi Contructor method executed with this params:{"param1":1,"param2":2}', $body->json->data);
    }

    public function testWorkerCommand(): void
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'job:test';

        $job = new Job();
        $job->command($command);
        $job->setPriority(10)->setTtr(3600);
        $result = $job->enqueue('default');

        $this->assertSame('string', gettype($result));

        command('queues:worker default --oneTime');

        $this->assertSame('Commands can output text. []', Services::response()->getBody());
    }

    public function testWorkerShell(): void
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $result = $job->enqueue('default');

        $this->assertSame('string', gettype($result));

        command('queues:worker default --oneTime');

        /** @var array $body */
        $body = Services::response()->getBody();
        $this->assertContains('src', $body);
    }

    public function testWorkerEvent(): void
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('default');

        $this->assertSame('string', gettype($result));

        command('queues:worker default --oneTime');

        $this->assertTrue(Services::response()->getBody());
    }

    public function testWorkerUrl(): void
    {
        $this->injectMockQueueWorker('beanstalk');

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

        $this->assertSame('string', gettype($result));

        command('queues:worker default --oneTime');

        $this->assertIsObject(Services::response()->getBody());

        /** @var object $response */
        $response = Services::response()->getBody();
        $this->assertSame('https://httpbin.org/post', $response->url);
        $this->assertSame('p1', $response->json->param1);
    }

    public function testScheduledShell(): void
    {
        $this->injectMockQueueWorker('beanstalk');

        $command = 'pwd';

        $dateTimeObj = new DateTime('now');
        $dateTimeObj->add(new DateInterval('PT2H'));

        $job = new Job();
        $job->shell($command);
        $job->scheduled($dateTimeObj);

        $job->setToDefaultQueue();
        $result = $job->enqueue();

        $this->assertSame('string', gettype($result));
    }
}
