<?php

namespace Daycry\Queues\Config;

use Config\Validation as BaseValidation;

class QueueValidation extends BaseValidation
{
    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    public $dataQueue = [
        'type' => 'required|string|in_list[class,command,shell,closure,event,url]',
        'queue' => 'required|string',
        'delay' => 'required|integer|is_natural',
        'priority' => 'required|integer|is_natural',
        'ttr' => 'required|integer|is_natural',
        'params' => 'if_exist|required'
    ];
}
