<?php

namespace Daycry\Queues\Exceptions;

class DataStructureException extends \RuntimeException
{
    protected $code = 400;

    public static function validationError($errors)
    {
        return new self($errors);
    }

    public static function invalidQueue(string $queue)
    {
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'queue' => $queue ))->renderString(lang('Queue.invalidQueue')));
    }
}