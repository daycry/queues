<?php

declare(strict_types=1);

namespace Daycry\Queues\Traits;

use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use Daycry\Queues\Exceptions\JobException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

trait ExecutableTrait
{
    /**
     * Runs this Job's action.
     *
     * @throws CronJobException
     */
    public function run()
    {
        $method = 'run' . ucfirst($this->type);
        // @codeCoverageIgnoreStart
        if (!method_exists($this, $method)) {
            throw JobException::forInvalidTaskType($this->type);
        }
        // @codeCoverageIgnoreEnd
        return $this->$method();
    }

    /**
     * Runs a framework Command.
     *
     * @return string Buffered output from the Command
     * @throws \InvalidArgumentException
     */
    protected function runCommand(): mixed
    {
        return command($this->getAction()->command);
    }

    /**
     * Executes a shell script.
     *
     * @return array Lines of output from exec
     */
    protected function runShell(): mixed
    {
        exec($this->getAction()->command, $output);

        return $output;
    }

    /**
     * Triggers an Event.
     *
     * @return boolean Result of the trigger
     */
    protected function runEvent(): mixed
    {
        return Events::trigger($this->getAction()->event);
    }

    /**
     * Run a class.
     *
     * @return boolean Result of the trigger
     */
    protected function runClasses(): mixed
    {
        $inConstructor = (isset($this->getAction()->options->constructor)) ? true : false;
        $inMethod = (isset($this->getAction()->options->method)) ? true : false;
        $class = $this->getAction()->class;

        $class = ($inConstructor) ? new $class($this->getAction()->options->constructor) : new $class();

        $return = ($inMethod) ? $class->{$this->getAction()->method}($this->getAction()->options->method) : $class->{$this->getAction()->method}();
        
        return $return;
    }

    /**
     * Queries a URL.
     *
     * @return mixed|string Body of the Response
     */
    protected function runUrl(): mixed
    {
        $data = $this->getAction();

        $verify = (isset($data->verify)) ? $data->verify : true;
        $headers = (isset($data->headers)) ? (array)$data->headers : [];
        $body = (isset($data->body)) ? \json_decode(\json_encode($data->body), true) : [];
        $dataType = (isset($data->dataType)) ? $data->dataType : 'json';

        $headers['Host'] = $this->_getHost($data->url);

        $options = [
            'verify'        => $verify,
            'allow_redirects' => true,
            'http_errors'   => true,
            'timeout'       => 3600,
            'headers'       => $headers,
            $dataType       => $body,
        ];

        $r = Services::response(null, true);

        try {
            $client = new Client();
            $response = $client->request(\strtoupper($data->method), $data->url, $options);
        // @codeCoverageIgnoreStart
        } catch(RequestException $ex) {
            $response = $ex->getResponse();

        } catch(ClientException $ex) {
            $response = $ex->getResponse();
        }
        // @codeCoverageIgnoreEnd
        $r->setStatusCode($response->getStatusCode());
        $r->setBody(\json_decode($response->getBody()->getContents()));

        return $r;
    }

    private function _getHost($Address)
    {
        $parseUrl = parse_url(trim($Address));
        return trim(isset($parseUrl['host']) ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
    }
}