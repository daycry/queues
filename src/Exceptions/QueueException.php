<?php

declare(strict_types=1);

namespace Daycry\Queues\Exceptions;

use RuntimeException;

class QueueException extends RuntimeException
{
    public static function forInvalidWorker(string $worker)
    {
        return new self(lang('Job.invalidWorker', [ $worker ]));
    }

    public static function forInvalidQueue(string $queue)
    {
        return new self(lang('Job.invalidQueue', [ $queue ]));
    }

    public static function forInvalidConnection(string $error)
    {
        return new self($error);
    }

}
