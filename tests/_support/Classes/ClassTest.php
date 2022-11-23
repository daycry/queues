<?php

namespace Tests\Support\Classes;

use CodeIgniter\CLI\CLI;

class ClassTest
{
    public function __construct() {}

    public function myMethod(object $params)
    {
        CLI::write('Class can output text with params: .' . \json_encode($params));
        return true;
    }
}