<?php

declare(strict_types=1);

namespace Daycry\Queues\Exceptions;

use Daycry\Queues\Interfaces\BaseExceptionInterface;

class RuntimeException extends \RuntimeException implements BaseExceptionInterface
{
}