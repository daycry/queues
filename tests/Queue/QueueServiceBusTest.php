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
use Daycry\PHPUnit\Vcr\Attributes\UseCassette;
use Daycry\Queues\Job;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Classes\Example;
use Tests\Support\TestCase;

/**
 * @internal
 */
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

    #[Group('fixtures')]
    #[UseCassette('testCommandServiceBus.json')]
    public function testCommand(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'job:test';

        $job = new Job();
        $job->command($command);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    #[Group('fixtures')]
    #[UseCassette('testWorkerCommandServiceBus.json')]
    public function testWorkerCommand(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertSame('Commands can output text. []', Services::response()->getBody());
    }

    #[Group('fixtures')]
    #[UseCassette('testClassesServiceBus.json')]
    public function testClasses(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        $job = new Job();
        $job->classes(Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]]);
        $job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' => ['X-API-KEY' => '1234']]);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    #[Group('fixtures')]
    #[UseCassette('testWorkerClassesServiceBus.json')]
    public function testWorkerClasses(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertSame('Hi Contructor method executed with this params:{"param1":1,"param2":2}', Services::response()->getBody()->json->data);
    }

    #[Group('fixtures')]
    #[UseCassette('testShellServiceBus.json')]
    public function testShell(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'ls';

        $job = new Job();
        $job->shell($command);
        $job->setLabel('test-shell');
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    #[Group('fixtures')]
    #[UseCassette('testWorkerShellServiceBus.json')]
    public function testWorkerShell(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertContains('src', Services::response()->getBody());
    }

    #[Group('fixtures')]
    #[UseCassette('testEventServiceBus.json')]
    public function testEvent(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        $command = 'custom';

        $job = new Job();
        $job->event($command);
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    #[Group('fixtures')]
    #[UseCassette('testWorkerEventServiceBus.json')]
    public function testWorkerEvent(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertTrue(Services::response()->getBody());
    }

    #[Group('fixtures')]
    #[UseCassette('testUrlServiceBus.json')]
    public function testUrl(): void
    {
        $this->injectMockQueueWorker('serviceBus');

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
        $result = $job->enqueue('dummy');

        $this->assertIsString($result);
    }

    #[Group('fixtures')]
    #[UseCassette('testWorkerUrlServiceBus.json')]
    public function testWorkerUrl(): void
    {
        $this->injectMockQueueWorker('serviceBus');

        command('queues:worker dummy --oneTime');

        $this->assertSame('https://httpbin.org/post', Services::response()->getBody()->url);
        $this->assertSame('p1', Services::response()->getBody()->json->param1);
    }

    /*
    #[UseCassette('testScheduledShellServiceBus.json')]
    #[Group('fixtures')]
    public function testScheduledShell()
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
