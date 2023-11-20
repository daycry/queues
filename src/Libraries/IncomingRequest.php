<?php

declare(strict_types=1);

namespace Daycry\Queues\Libraries;

use CodeIgniter\HTTP\IncomingRequest as BaseIncomingRequest;
use Config\App;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;

class IncomingRequest extends BaseIncomingRequest
{
    /**
     * Constructor
     *
     * @param App         $config
     * @param string|null $body
     */
    public function __construct($config, ?URI $uri = null, $body = 'php://input', ?UserAgent $userAgent = null)
    {
        parent::__construct($config, $uri, $body, $userAgent);
    }

    public function getParsedHeaders()
    {
        return array_map(
            function ($header) {
                return $header->getValueLine();
            },
            $this->headers()
        );
    }
}
