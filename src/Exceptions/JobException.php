<?php

declare(strict_types=1);

namespace Daycry\Queues\Exceptions;

use RuntimeException;

class JobException extends RuntimeException
{
    public static function forInvalidTaskType(string $type)
    {
        return new self(lang('Job.invalidJobType', [ $type ]));
    }
    
    public static function forInvalidMethod(string $method)
    {
        return new self(lang('HTTP.methodNotFound', [$method]));
    }

    public static function validationError($errors)
    {
        return new self($errors);
    }
}
