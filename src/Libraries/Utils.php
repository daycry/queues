<?php

namespace Daycry\Queues\Libraries;

use Config\Services;
use Daycry\Queues\Exceptions\JobException;
use Daycry\Queues\Exceptions\QueueException;

class Utils
{
    public static function checkDataQueue(array|object $data, string $rule)
    {
        if(!is_array($data))
        {
            $data = json_decode(json_encode($data), true);
        }

        $validator = Services::validation(null, false);

        if (!$validator->reset()->run($data, $rule) ) {
            throw JobException::validationError($validator->listErrors());
        }
    }

    public static function parseConfigFile($attr): array
    {
        if ($attr && ! is_array($attr)) {
            $attr = explode(',', $attr);
        }

        return array_map('trim', $attr);
    }
}