<?php

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
