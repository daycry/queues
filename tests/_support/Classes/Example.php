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

namespace Tests\Support\Classes;

class Example
{
    private ?string $paramConstruct = null;

    public function __construct(string $param)
    {
        $this->paramConstruct = $param;
    }

    public function run(array|object $params = [])
    {
        return 'Hi ' . $this->paramConstruct . ' method executed with this params:' . json_encode($params);
    }
}
