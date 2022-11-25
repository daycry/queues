<?php

namespace Daycry\Queues\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Daycry\Queues\Interfaces\JobInterface;

class Api implements JobInterface
{
    protected object $params;

    public function __construct(object $params)
    {
       $this->params = $params;
    }

    public function execute()
    {
        $verify = (isset($this->params->verify)) ? $this->params->verify : true;

        $this->params->body = ($this->params->body) ? array($this->params->type => (array)$this->params->body) : [];

        $client = new Client(['verify' => $verify ]);
        $request = new Request($this->params->method, $this->params->url, (array)$this->params->headers, json_encode($this->params->body));

        return \json_decode(($client->send($request, ['timeout' => 10]))->getBody()->getContents());
    }
}