<?php

namespace Daycry\Queues\Interfaces;

interface JobInterface
{
    public function __construct(object $params);
    public function execute();
}
