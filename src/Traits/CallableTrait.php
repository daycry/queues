<?php

declare(strict_types=1);

namespace Daycry\Queues\Traits;

use Daycry\Queues\Job;
use stdClass;

trait CallableTrait
{
    protected ?object $callback = null;

    public function setCallback(string $url, array $options = []): self
    {
        $this->callback = new stdClass();
        $this->callback->url = $url;
        $this->callback->options = $options;

        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }


}