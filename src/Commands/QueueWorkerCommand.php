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
use CodeIgniter\HTTP\Response;
use Config\Services;
use Daycry\Exceptions\Interfaces\BaseExceptionInterface;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Job;
use Daycry\Queues\Libraries\Utils;
use Exception;

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
        $queue = $params[0] ?? CLI::getOption('queue');

        // CLI::write('Queue "'. $queue .'" started successfully.', 'green');

        $oneTime = false;
        if (array_key_exists('oneTime', $params) || CLI::getOption('oneTime')) {
            $oneTime = true;
        }

        // @codeCoverageIgnoreStart
        if (empty($queue)) {
            $queue = CLI::prompt(lang('Queue.insertQueue'));
        }
        // @codeCoverageIgnoreEnd

        while (true) {
            $queues = Utils::parseConfigFile(service('settings')->get('Queue.queues'));

            $response = [];

            Services::resetSingle('request');
            Services::resetSingle('response');

            try {
                $workers = service('settings')->get('Queue.workers');
                $worker  = service('settings')->get('Queue.worker');

                if (! array_key_exists($worker, $workers)) {
                    throw QueueException::forInvalidWorker($worker);
                }

                $worker = new $workers[$worker]();

                $job = $worker->watch($queue);

                if (isset($job)) {
                    $this->locked = true;

                    $dataJob = $worker->getDataJob();
                    $j       = new Job($dataJob);

                    $this->earlyChecks($j);

                    $result = $j->run();

                    $response['status'] = true;

                    if (! $result instanceof Response) {
                        $result = (Services::response(null, true))->setStatusCode(200)->setBody($result);
                    }

                    $response['statusCode'] = $result->getStatusCode();
                    $response['data']       = $result->getBody();

                    $this->lateChecks($j);
                }
            } catch (BaseExceptionInterface $e) {
                $response['statusCode'] = $e->getCode();
                $response['error']      = $e->getMessage();
                $response['status']     = false;
                $worker->removeJob($j, true);
                $this->showError($e);
            } catch (Exception $e) {
                $response['statusCode'] = $e->getCode();
                $response['error']      = $e->getMessage();
                $response['status']     = false;
            }

            if ($response && isset($job)) {
                try {
                    if ($response['status'] === true || $j->getAttempt() >= service('settings')->get('Queue.maxAttempts')) {
                        $worker->removeJob($j, false);
                    }

                    // callback
                    if ($cb = $j->getCallback()) {
                        $cb->options->body = $response;
                        $c                 = new Job();
                        $c->url($cb->url, $cb->options);

                        $this->earlyCallbackChecks($c);
                        $r = $c->run();
                        $this->lateCallbackChecks($c);
                    }
                } catch (BaseExceptionInterface $e) {
                    $this->showError($e);
                }
            }

            $this->locked = false;
            $response     = [];
            unset($j, $job);

            sleep(service('settings')->get('Queue.waitingTimeBetweenJobs'));

            if ($oneTime) {
                return;
            }
        }
    }
}
