[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

# Queues for Codeigniter 4
Codeigniter 4 with beanstalk, redis, sync & service bus (azure) queues

[![Build Status](https://github.com/daycry/queues/workflows/PHP%20Tests/badge.svg)](https://github.com/daycry/queues/actions?query=workflow%3A%22PHP+Tests%22)
[![Coverage Status](https://coveralls.io/repos/github/daycry/queues/badge.svg?branch=master)](https://coveralls.io/github/daycry/queues?branch=master)
[![Downloads](https://poser.pugx.org/daycry/queues/downloads)](https://packagist.org/packages/daycry/queues)
[![GitHub release (latest by date)](https://img.shields.io/github/v/release/daycry/queues)](https://packagist.org/packages/daycry/queues)
[![GitHub stars](https://img.shields.io/github/stars/daycry/queues)](https://packagist.org/packages/daycry/queues)
[![GitHub license](https://img.shields.io/github/license/daycry/queues)](https://github.com/daycry/queues/blob/master/LICENSE)

## Installation via composer

Use the package with composer install

	> composer require daycry/queues

## Configuration

Run command:

	> php spark queues:publish

This command will copy a config file to your app namespace.
Then you can adjust it to your needs. By default file will be present in `app/Config/Queue.php`.

Allowed tasks

| Tasks         |
|:--------------|
| url           |
| command       |
| classes       |
| shell         |

## Usage Job Class

URL

```php
use Daycry\Queues\Job;

$job = new Job();
$job = $producer->setQueue('default')
    ->url('https://httpbin.org/post', [
            'verify' => false,
            'method' => 'post',
            'body' => ['param1' => 'p1'],
            'dataType' => 'json',
            'headers' => [
                'X-API-KEY' => '1234'
            ]
        )
    ->enqueue();

```

COMMAND

```php
use Daycry\Queues\Job;

$job = new Job();
$job = $producer->command('foo:bar')->enqueue('default');

```

CLASSES

```php

use Daycry\Queues\Job;

$job = new Job();
$job->classes(\Tests\Support\Classes\Example::class, 'run', ['constructor' => 'Contructor', 'method' => ['param1' => 1, 'param2' => 2]])->enqueue('default');

```

You can pass options in third parameter that contains paraterms in construct function and/or method.

SHELL

```php

use Daycry\Queues\Job;

$job = new Job();
$job->shell('ls')->enqueue('default');

```

## Job Scheduled

```php

$dateTimeObj= new DateTime('now');
$dateTimeObj->add(new DateInterval("PT1H"));

$job = new Job();
$job->shell('ls');
$job->scheduled($dateTimeObj);
$result = $job->enqueue('default');

```

## Job Callback

You can configure a callback using 'URL' type.

```php

$job->setCallback('https://httpbin.org/post', ['method' => 'post', 'headers' =>['X-API-KEY' => '1234']]);

```

## Custom Methods

Beanstalk and Service Bus have custom methods

BEANSTALK

```php

use Daycry\Queues\Job;

$producer = new Job();
$job->shell('ls')->setPriority(10)->setTtr(3600)->enqueue('default');

```

SERVICE BUS

```php

use Daycry\Queues\Job;

$producer = new Job();
$job->shell('ls')->setLabel('label')->enqueue('default');

```

## Usage Worker

    > cd /path-to-your-project && php spark queues:worker default

On default is the name of queue.


If you want to execute only one time the worker, you can do this:

    > cd /path-to-your-project && php spark queues:worker default --oneTime

In order to use these functions, the class must be extended.

You can extends the worker command for customize early and late methods.
```php
use Daycry\Queues\Job;

$this->earlyChecks(Job $j);

//job execution

$this->lateChecks($j);

$this->earlyCallbackChecks(Job $j);

//callback execution

$this->lateCallbackChecks($j);
```
