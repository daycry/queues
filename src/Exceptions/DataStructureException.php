<?php

namespace Daycry\Queues\Exceptions;

class DataStructureException extends \RuntimeException
{
    protected $code = 400;

    public static function validationError(array $errors )
    {
        return new self($errors);
    }
}