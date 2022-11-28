[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

# Queues for Codeigniter 4 (using Beanstalk)
Codeigniter 4 with beanstalk queue

[![Build Status](https://github.com/daycry/queues/workflows/PHP%20Tests/badge.svg)](https://github.com/daycry/queues/actions?query=workflow%3A%22PHP+Tests%22)
[![Coverage Status](https://coveralls.io/repos/github/daycry/queues/badge.svg?branch=master)](https://coveralls.io/github/daycry/queues?branch=master)
[![Downloads](https://poser.pugx.org/daycry/queues/downloads)](https://packagist.org/packages/daycry/queues)
[![GitHub release (latest by date)](https://img.shields.io/github/v/release/daycry/queues)](https://packagist.org/packages/daycry/queues)
[![GitHub stars](https://img.shields.io/github/stars/daycry/queues)](https://packagist.org/packages/daycry/queues)
[![GitHub license](https://img.shields.io/github/license/daycry/queues)](https://github.com/daycry/queues/blob/master/LICENSE)

## Installation via composer

Use the package with composer install

	> composer require daycry/queues

## Manual installation

Download this repo and then enable it by editing **app/Config/Autoload.php** and adding the **Daycry\Queues**
namespace to the **$psr4** array. For example, if you copied it into **app/ThirdParty**:

```php
$psr4 = [
    'Config'      => APPPATH . 'Config',
    APP_NAMESPACE => APPPATH,
    'Daycry\Queues' => APPPATH .'ThirdParty/queues/src',
];
```

## Configuration

Run command:

	> php spark queue:publish

This command will copy a config file to your app namespace.
Then you can adjust it to your needs. By default file will be present in `app/Config/Queue.php`.

Allowed tasks

| Tasks         |
|:--------------|
| api           |
| command       |
| classes       |
| shell         |
| url           |

## Usage Producer Class

API

```php

$producer = new Producer();
$job = $producer->setQueue('default')->setPriority(10)->setTtr(3600)->setDelay(0)->setType('api')->setParams(
    array(
        'verify' => false,
        'url' => 'https://httpbin.org/post',
        'method' => 'post',
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'X-API-KEY' => '1234'],
        'type' => 'json',
        'body' => ['name' => 'daycry']
    )
)->createJob();

```

COMMAND

```php

$producer = new Producer();
$job = $producer->setQueue('default')->setPriority(10)->setDelay(0)->setTtr(3600)->setType('command')->setParams(
    array(
        'command' => 'job:test'
    )
)->createJo();

```

CLASSES

```php

$producer = new Producer();
$job = $producer->setQueue('default')->setPriority(10)->setDelay(0)->setTtr(3600)->setType('classes')->setParams(
    array(
        'class' => \Tests\Support\Classes\ClassTest::class, 
        'method' => 'myMethod',
        'params' => array('param1' => 'pa1')
    )
)->createJob();

```

SHELL

```php

$producer = new Producer();
$job = $producer->setQueue('default')->setPriority(10)->setDelay(0)->setTtr(3600)->setType('shell')->setParams(
    array(
        'command' => 'ls -lisa'
    )
)->createJob();

```

URL

```php

$producer = new Producer();
$job = $producer->setQueue('default')->setPriority(10)->setDelay(0)->setTtr(3600)->setType('url')->setParams(
    array(
        'url' => 'https://github.com/'
    )
)->createJob();

```

You can pass the configuration class as a parameter in case you want to customize an attribute 

```php
$config = config('Queue');
$producer = new Producer($config);

```

## Usage Worker

    > * * * * * cd /path-to-your-project && php spark queue:run >> /dev/null 2>&1

If you want change or extend worker class, you can edit in config file.

```php
public $worker = \Daycry\Queues\Libraries\Worker::class;
```

In order to use these functions, the class must be extended.

```php
<?php

namespace App\Libraries;

class Worker extends \Daycry\Queues\Libraries\Worker
{
    public function __construct(?Queue $config = null)
    {
        $this->benchmark = Services::timer();

        parent::__construct($config);
    }

    protected function preActionJob(Job $job = null)
    {
        $this->benchmark->start('job');
    }

    protected function postActionJob(Job $job = null, $result)
    {
        $this->benchmark->stop('job');
    }
}
```
