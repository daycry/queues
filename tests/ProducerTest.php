<?php

use CodeIgniter\Test\CIUnitTestCase;
use Daycry\Queues\libraries\Producer;
use Daycry\Queues\Config\Queue;
use Pheanstalk\Job;
use Pheanstalk\Exception\ClientException;

/**
 * @internal
 */
final class ProducerTest extends CIUnitTestCase
{
    private Queue $config;

    protected function setUp(): void
    {
        $this->config = new Queue();
        parent::setUp();
    }

    public function testProducerError()
    {
        $this->expectException(ClientException::class);

        $this->config->port = 11301;

        $producer = new Producer($this->config);
        $job = $producer->setDelay(100)->setType('command')->setParams(array('hola' => 'hola'))->createJob();
    }
    
    public function testProducer()
    {
        $producer = new Producer($this->config);
        $job = $producer->setDelay(100)->setType('command')->setParams(array('hola' => 'hola'))->createJob();

        $this->assertInstanceOf(Job::class, $job);
    }

    /*public function testListTubesWorker()
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
    }*/
}