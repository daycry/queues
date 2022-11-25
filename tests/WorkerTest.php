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

    public function testWorkerError()
    {
        $this->expectException(ClientException::class);

        $this->config->port = 11301;
        $status = (new Worker($this->config))->watch();
    }

    public function testWorkerCommand()
    {
        $status = (new Worker($this->config))->watch();
        $this->assertSame('1', $status);
    }

    public function testWorkerClass()
    {
        $status = (new Worker($this->config))->watch();
        $this->assertTrue($status);
    }

    public function testWorkerShell()
    {
        $status = (new Worker($this->config))->watch();
        $this->assertArrayHasKey(0, $status);
        $this->assertStringStartsWith('total', $status[0]);
    }

    public function testWorkerUrl()
    {
        $status = (new Worker($this->config))->watch();
        $this->assertStringContainsString('<!DOCTYPE html>', $status);
    }

    public function testWorkerApi()
    {
        $status = (new Worker($this->config))->watch();
        $this->assertObjectHasAttribute('data', $status);
    }
}