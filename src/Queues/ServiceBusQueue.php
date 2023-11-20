<?php

declare(strict_types=1);

namespace Daycry\Queues\Queues;

use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Interfaces\WorkerInterface;
use Daycry\Queues\Libraries\ServiceBusHeaders as LibrariesServiceBusHeaders;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Daycry\Queues\Job as QueuesJob;

class ServiceBusQueue extends BaseQueue implements QueueInterface, WorkerInterface
{
    private string $url;
    private LibrariesServiceBusHeaders $serviceBusHeaders;
    private ?array $job = null;

    public function __construct()
    {
        $config = service('settings')->get('Queue.serviceBus');
        $this->url = $config['url'];
        $this->serviceBusHeaders = (new LibrariesServiceBusHeaders())->generateMessageId()->generateSasToken($this->url, $config['issuer'], $config['secret']);
    }
    
    public function enqueue(object $data, string $queue = 'default')
    {
        $this->calculateDelay($data);

        if($this->getDelay() > 0)
        {
            $this->serviceBusHeaders->schedule($data->schedule);
        }

        $response = $this->request($queue . '/messages', 'post', $data, $this->serviceBusHeaders->getHeaders());

        return $this->serviceBusHeaders->getMessageId();
    }

    public function setLabel(string $label)
    {
        $this->serviceBusHeaders->setLabel($label);
    }

    public function watch(string $queue)
    {
        $url = $queue . '/messages/head';

        $response = $this->request($url, 'delete', [], $this->serviceBusHeaders->getHeaders());

        if($response) {
            $this->job = $response;

            return $this->job;
        }

        return false;
    }

    public function removeJob(QueuesJob $job, bool $recreate = false): bool
    {
        if($recreate === true)
        {
            $job->addAttempt();
            $job->enqueue($job->getQueue());
        }

        $this->job = null;

        return true;
    }

    public function getDataJob()
    {
        return $this->job['body'];
    }

    protected function request(string $url, string $method, object|array $body = [], array $headers = []): array
    {
        if(strpos($url, 'https://') !== 0) {
            $url = $this->url . $url;
        }

        $client  = new Client(['verify' => false]);
        $request = new Request($method, $url, $headers, \json_encode($body));

        try {
            $response = $client->send($request, ['timeout' => 10]);

            if( $response )
            {
                return [
                    'body' => json_decode($response->getBody()->getContents()),
                    'status' => $response->getStatusCode(),
                    'headers' => $response->getHeaders()
                ];
            }

            return null;

        } catch (RequestException $e) {
            throw QueueException::forInvalidConnection($e->getMessage());
        }
    }
}