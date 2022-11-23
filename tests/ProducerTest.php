<?php

use CodeIgniter\Test\CIUnitTestCase;
use Daycry\Queues\libraries\Producer;
use Daycry\Queues\Config\Queue;
use Pheanstalk\Job;
use Pheanstalk\Exception\ClientException;
use Daycry\Queues\Exceptions\DataStructureException;

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
        $job = $producer->setDelay(0)->setType('command')->setParams(array('command' => 'job:test'))->createJob();
    }
    
    public function testProducerParamsError()
    {
        $this->expectException(DataStructureException::class);

        $producer = new Producer($this->config);
        $job = $producer->setDelay(0)->setType('command')->setParams(array('command1' => 'job:test'))->createJob();

        $this->assertInstanceOf(Job::class, $job);
    }

    public function testProducerCommand()
    {
        $producer = new Producer($this->config);
        $job = $producer->setDelay(0)->setType('command')->setParams(array('command' => 'job:test'))->createJob();

        $this->assertInstanceOf(Job::class, $job);
    }

    public function testProducerClassError()
    {
        $this->expectException(DataStructureException::class);

        $producer = new Producer($this->config);
        $job = $producer->setDelay(0)->setType('classes')->setParams(
            array(
                'class1' => \Tests\Support\Classes\ClassTest::class, 
                'method' => 'myMethod',
                'params' => array('param1' => 'pa1')
            ))->createJob();
    }

    public function testProducerClass()
    {
        $producer = new Producer($this->config);
        $job = $producer->setDelay(0)->setType('classes')->setParams(
            array(
                'class' => \Tests\Support\Classes\ClassTest::class, 
                'method' => 'myMethod',
                'params' => array('param1' => 'pa1')
            ))->createJob();

        $this->assertInstanceOf(Job::class, $job);
    }

    public function testProducerShell()
    {
        $producer = new Producer($this->config);
        $job = $producer->setDelay(0)->setType('shell')->setParams(array('command' => 'ls -lisa'))->createJob();

        $this->assertInstanceOf(Job::class, $job);
    }
}