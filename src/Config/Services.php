<?php

declare(strict_types=1);

namespace Daycry\Queues\Config;

use Config\Services as BaseService;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Daycry\Queues\Libraries\IncomingRequest;
use CodeIgniter\HTTP\CLIRequest;

class Services extends BaseService
{
    /**
     * Returns the current Request object.
     *
     * createRequest() injects IncomingRequest or CLIRequest.
     *
     * @return CLIRequest|IncomingRequest
     *
     * @deprecated The parameter $config and $getShared are deprecated.
     */
    public static function request(?App $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('request', $config);
        }

        // @TODO remove the following code for backward compatibility
        return static::incomingrequest($config, $getShared);
    }

    /**
     * The IncomingRequest class models an HTTP request.
     *
     * @return IncomingRequest
     *
     * @internal
     */
    public static function incomingrequest(?App $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('request', $config);
        }

        $config ??= config('App');

        return new IncomingRequest(
            $config,
            self::uri(),
            'php://input',
            new UserAgent()
        );
    }
}
