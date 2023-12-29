<?php

declare(strict_types=1);

namespace Daycry\Queues\Queues;

use DateTime;

abstract class BaseQueue
{
    private int $delay = 0;

    public function calculateDelay(object $data): void
    {
        if(isset($data->schedule)) {
            $now = new DateTime('now');
            $schedule = new DateTime($data->schedule->date);

            $delay = $schedule->getTimestamp() - $now->getTimestamp();

            $delay = ($delay > 0) ? $delay : 0;

            $this->setDelay($delay);
        }
    }

    protected function setDelay(int $delay)
    {
        $this->delay = $delay;

        return $this;
    }

    protected function getDelay()
    {
        return $this->delay;
    }
}
