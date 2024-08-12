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

namespace Daycry\Queues\Libraries;

use Config\Services;
use Daycry\Queues\Config\Validation;
use Daycry\Queues\Exceptions\JobException;

class Utils
{
    public static function checkDataQueue(array|object $data, string $rule): void
    {
        if (! is_array($data)) {
            $data = json_decode(json_encode($data), true);
        }

        $validator = Services::validation(config(Validation::class), false);

        if (! $validator->reset()->run($data, $rule)) {
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
