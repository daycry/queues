[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

# Queues for Codeigniter 4 (using Beanstalk)
Codeigniter 4 with beanstalk queue

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

## Usage Producer Class

```php

$producer = new Producer();
$job = $producer->setDelay(0)->setType('api')->setParams(
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

## Usage Worker

    > php spark queue:run