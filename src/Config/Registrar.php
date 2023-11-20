<?php

declare(strict_types=1);

namespace Daycry\Queues\Config;

class Registrar
{
    /**
     * Register validation.
     */
    public static function Validation(): array
    {
        return [
            'queueData' => [
                'queue' => 'required|string',
                'type' => 'required|string',
                'action' => 'required',
                'schedule' => 'if_exist|permit_empty',
                'attempts' => 'if_exist|is_natural',
                'callback'  => 'if_exist|permit_empty',
                'callback.url'  => 'if_exist|required_with[callback]|valid_url_strict[https]',
                'callback.options'  => 'if_exist|required_with[callback]',
            ],
            'url' => [
                'verify' => 'if_exist',
                'url' => 'required|valid_url',
                'method' => 'required|string',
                'dataType' => 'if_exist|required|string',
                'headers' => 'if_exist|required',
                'body' => 'if_exist|required'
            ],
            'classes' => [
                'class' => 'required|string',
                'method' => 'required|string',
                'params' => 'if_exist|permit_empty',
                'params.contructor' => 'if_exist|required_with[params]|required',
                'params.method' => 'if_exist|required_with[params]|required',
            ],
            'command' => [
                'command' => 'required|string',
            ],
            'shell' => [
                'command' => 'required|string',
            ],
            'event' => [
                'event' => 'required|string',
            ]
        ];
    }
}
