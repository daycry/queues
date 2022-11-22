<?php

use CodeIgniter\Test\CIUnitTestCase;
use Daycry\Queues\libraries\Worker;
use Daycry\Queues\Config\Queue;
use Pheanstalk\Exception\ClientException;

/**
 * @internal
 */
final class WorkerTest extends CIUnitTestCase
{
    private Queue $config;

    protected function setUp(): void
    {
        $this->config = new Queue();
        parent::setUp();
    }

    public function testListTubesWorker()
    {
        $tubes = (new Worker($this->config))->listTubes();
        $this->assertContains('default', $tubes);
    }

    public function testWorker()
    {
        $status = (new Worker($this->config))->watch();
        $this->assertTrue($status);
    }

    public function testWorkerError()
    {
        $this->expectException(ClientException::class);

        $this->config->port = 11301;
        $job = new Worker($this->config);
    }
}