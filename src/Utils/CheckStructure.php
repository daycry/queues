<?php

namespace Daycry\Queues\Utils;

use Daycry\Queues\Exceptions\DataStructureException;
use Daycry\Queues\Config\QueueValidation;

class CheckStructure
{
    public static function checkDataQueue(array $data)
    {
        $validator = \Config\Services::validation(config(QueueValidation::class), false);

        if (!$validator->reset()->run($data, 'dataQueue') ) {
            throw DataStructureException::validationError($validator->listErrors());
        }

        self::checkDataJob($data);
    }

    public static function checkDataJob(array $data)
    {
        $validator = \Config\Services::validation(config(QueueValidation::class), false);

        if (!$validator->reset()->run($data['params'], $data['type'])) {
            throw DataStructureException::validationError($validator->listErrors());
        }

    }
}