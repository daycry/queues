<?php

namespace Daycry\Queues\Config;

use Config\Validation as BaseValidation;

class QueueValidation extends BaseValidation
{
    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    public $dataQueue = [
        'type' => 'required|string|in_list[classes,command,shell,closure,event,url,api]',
        'queue' => 'required|string',
        'delay' => 'required|integer|is_natural',
        'priority' => 'required|integer|is_natural',
        'ttr' => 'required|integer|is_natural',
        'params' => 'required'
    ];

    public $classes = [
        'class' => 'required',
        'method' => 'required|string',
        'params' => 'if_exist|required'
    ];

    public $command = [
        'command' => 'required|string'
    ];

    public $shell = [
        'command' => 'required|string'
    ];
}
