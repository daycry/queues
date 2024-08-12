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

namespace Daycry\Queues;

use CodeIgniter\I18n\Time;
use DateTime;
use DateTimeZone;
use Daycry\Queues\Exceptions\JobException;
use Daycry\Queues\Traits\CallableTrait;
use Daycry\Queues\Traits\EnqueuableTrait;
use Daycry\Queues\Traits\ExecutableTrait;

class Job
{
    use EnqueuableTrait;
    use ExecutableTrait;
    use CallableTrait;

    private array $types          = [];
    protected ?string $type       = null;
    protected mixed $action       = null;
    protected ?DateTime $schedule = null;

    public function __construct(?object $data = null)
    {
        $this->types = service('settings')->get('Queue.jobTypes');

        $this->checkWorker();

        if ($data) {
            foreach ($data as $attribute => $value) {
                if (property_exists($this, $attribute)) {
                    if ($attribute === 'schedule') {
                        if ($value && $value instanceof object) {
                            $this->{$attribute} = new DateTime($value->date, new DateTimeZone($value->timezone));
                        }
                    } else {
                        $this->{$attribute} = $value;
                    }
                }
            }

            if ($this->type && ! in_array($this->type, $this->types, true)) {
                throw JobException::forInvalidTaskType($this->type);
            }
        }
    }

    /**
     * Returns the type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the saved action.
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    public function command(string $command, array|object $options = []): Job
    {
        $this->type = 'command';
        $options    = $this->_prepareJobOptions($options);

        $this->action = json_decode(json_encode(['command' => $command, 'options' => $options]));

        return $this;
    }

    public function shell(string $command, array|object $options = []): Job
    {
        $this->type = 'shell';
        $options    = $this->_prepareJobOptions($options);

        $this->action = json_decode(json_encode(['command' => $command, 'options' => $options]));

        return $this;
    }

    /**
     * @param string $name Name of the event to trigger
     */
    public function event(string $name, array|object $options = []): Job
    {
        $this->type = 'event';
        $options    = $this->_prepareJobOptions($options);

        $this->action = json_decode(json_encode(['event' => $name, 'options' => $options]));

        return $this;
    }

    /**
     * @param array $options
     */
    public function url(string $url, array|object $options = []): Job
    {
        $data    = [];
        $options = $this->_prepareJobOptions($options);

        $data         = array_merge(['url' => $url], $options);
        $this->type   = 'url';
        $this->action = json_decode(json_encode($data));

        return $this;
    }

    /**
     * @param array $options
     */
    public function classes(string $class, string $method, array|object $options = []): Job
    {
        $data            = [];
        $data['class']   = $class;
        $data['method']  = $method;
        $data['options'] = $options;

        $this->type   = 'classes';
        $this->action = json_decode(json_encode($data));

        return $this;
    }

    public function scheduled(DateTime|Time $schedule)
    {
        if ($schedule instanceof Time) {
            $schedule = $schedule->toDateTime();
        }

        $this->schedule = $schedule;

        return $this;
    }

    public function toObject(): object
    {
        $data = get_object_vars($this);
        unset($data['types'], $data['worker']);

        return json_decode(json_encode($data));
    }

    private function _prepareJobOptions(array|object $options = [])
    {
        if ($options) {
            if (! is_array($options)) {
                $options = json_decode(json_encode($options), true);
            }

            return $options;
        }

        return [];
    }
}
