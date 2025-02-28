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

namespace Daycry\Queues\Exceptions;

use CodeIgniter\Exceptions\RuntimeException;

class JobException extends RuntimeException
{
    public static function forInvalidTaskType(string $type)
    {
        return new self(lang('Job.invalidJobType', [$type]));
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
