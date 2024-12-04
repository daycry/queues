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

namespace Daycry\Queues\Queues;

use DateTime;
use DateTimeZone;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Interfaces\WorkerInterface;
use Daycry\Queues\Job as QueuesJob;
use Daycry\Queues\Libraries\ServiceBusHeaders as LibrariesServiceBusHeaders;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class ServiceBusQueue extends BaseQueue implements QueueInterface, WorkerInterface
{
    private string $url;
    private LibrariesServiceBusHeaders $serviceBusHeaders;
    private ?array $job = null;

    public function __construct()
    {
        $config                  = service('settings')->get('Queue.serviceBus');
        $this->url               = $config['url'];
        $this->serviceBusHeaders = (new LibrariesServiceBusHeaders())->generateMessageId()->generateSasToken($this->url, $config['issuer'], $config['secret']);
    }

    public function enqueue(object $data, string $queue = 'default'): mixed
    {
        $this->calculateDelay($data);

        if ($this->getDelay() > 0) {
            $datetime = new DateTime($data->schedule->date, new DateTimeZone($data->schedule->timezone));
            $this->serviceBusHeaders->schedule($datetime);
        }

        $response = $this->request($queue . '/messages', 'post', $data, $this->serviceBusHeaders->getHeaders());

        return $this->serviceBusHeaders->getMessageId();
    }

    public function setLabel(string $label): void
    {
        $this->serviceBusHeaders->setLabel($label);
    }

    public function watch(string $queue)
    {
        $url = $queue . '/messages/head';

        $response = $this->request($url, 'delete', [], $this->serviceBusHeaders->getHeaders());

        if ($response) {
            $this->job = $response;

            return $this->job;
        }

        return false;
    }

    public function removeJob(QueuesJob $job, bool $recreate = false): bool
    {
        if ($recreate === true) {
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

    protected function request(string $url, string $method, array|object $body = [], array $headers = []): array
    {
        if (! str_starts_with($url, 'https://')) {
            $url = $this->url . $url;
        }

        $client  = new Client(['verify' => false]);
        $request = new Request($method, $url, $headers, \json_encode($body));

        try {
            $response = $client->send($request, ['timeout' => 10]);

            if ($response) {
                return [
                    'body'    => json_decode($response->getBody()->getContents()),
                    'status'  => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                ];
            }

            return null;
        } catch (RequestException $e) {
            throw QueueException::forInvalidConnection($e->getMessage());
        }
    }
}
