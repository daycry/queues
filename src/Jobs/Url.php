<?php

namespace Daycry\Queues\Jobs;

use GuzzleHttp\Client;
use Daycry\Queues\Interfaces\JobInterface;

class Url implements JobInterface
{
    protected object $params;

    public function __construct(object $params)
    {
       $this->params = $params;
    }

    public function execute()
    {
        $client = new Client();
        $response = $client->request('GET', $this->params->url);
        return $response->getBody();
    }
}