<?php

declare(strict_types=1);

/**
 * This file is part of Daycry Queues.
 *
 * (c) Daycry <daycry9@proton.me>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Daycry\Queues\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\HTTP\Response;
use Config\Services;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Job;
use Daycry\Queues\Libraries\Utils;

class QueueWorkerCommand extends BaseCommand
{
    protected $group       = 'Queues';
    protected $name        = 'queues:worker';
    protected $description = 'Start queue worker.';
    protected $usage       = 'queue:run <queue> [Options]';
    protected $arguments   = ['queue' => 'The queue name.'];
    protected $options     = ['--oneTime' => 'Only executes one time.'];
    protected bool $locked = false;

    protected function earlyChecks(Job $job): void
    {
    }

    protected function lateChecks(Job $job): void
    {
    }

    protected function earlyCallbackChecks(Job $job): void
    {
    }

    protected function lateCallbackChecks(Job $job): void
    {
    }

    public function run(array $params): void
    {
        $queue   = $params[0] ?? CLI::getOption('queue');
        $oneTime = array_key_exists('oneTime', $params) || CLI::getOption('oneTime');

        if (empty($queue)) {
            $queue = CLI::prompt(lang('Queue.insertQueue'));
        }

        while (true) {
            $this->processQueue($queue);

            if ($oneTime) {
                return;
            }

            sleep(service('settings')->get('Queue.waitingTimeBetweenJobs'));
        }
    }

    protected function processQueue(string $queue): void
    {
        $queues   = Utils::parseConfigFile(service('settings')->get('Queue.queues'));
        $response = [];

        Services::resetSingle('request');
        Services::resetSingle('response');

        try {
            $worker = $this->getWorker();

            $job = $worker->watch($queue);

            if (isset($job)) {
                $this->locked = true;

                $dataJob = $worker->getDataJob();
                $j       = new Job($dataJob);

                $this->earlyChecks($j);

                $result = $j->run();

                $response = $this->prepareResponse($result);

                $this->lateChecks($j);
            }
        } catch (ExceptionInterface $e) {
            $response = $this->handleException($e, $worker ?? null, $j ?? null);
        }

        if ($response && isset($job)) {
            $this->finalizeJob($response, $worker, $j);
        }

        $this->locked = false;
        unset($j, $job);
    }

    protected function getWorker()
    {
        $workers = service('settings')->get('Queue.workers');
        $worker  = service('settings')->get('Queue.worker');

        if (! array_key_exists($worker, $workers)) {
            throw QueueException::forInvalidWorker($worker);
        }

        return new $workers[$worker]();
    }

    protected function prepareResponse($result): array
    {
        $response['status'] = true;

        if (! $result instanceof Response) {
            $result = (Services::response(null, true))->setStatusCode(200)->setBody($result);
        }

        $response['statusCode'] = $result->getStatusCode();
        $response['data']       = $result->getBody();

        return $response;
    }

    protected function handleException($e, $worker, $job): array
    {
        $response['statusCode'] = $e->getCode();
        $response['error']      = $e->getMessage();
        $response['status']     = false;

        if ($worker && $job) {
            $worker->removeJob($job, true);
        }

        $this->showError($e);

        return $response;
    }

    protected function finalizeJob(array $response, $worker, Job $job): void
    {
        try {
            if ($response['status'] === true || $job->getAttempt() >= service('settings')->get('Queue.maxAttempts')) {
                $worker->removeJob($job, false);
            }

            if ($cb = $job->getCallback()) {
                $cb->options->body = $response;
                $c                 = new Job();
                $c->url($cb->url, $cb->options);

                $this->earlyCallbackChecks($c);
                $c->run();
                $this->lateCallbackChecks($c);
            }
        } catch (ExceptionInterface $e) {
            $this->showError($e);
        }
    }
}
