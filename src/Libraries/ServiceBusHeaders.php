<?php

namespace Daycry\Queues\Libraries;

use DateTime;
use DateTimeZone;

class ServiceBusHeaders
{
    private array $brokerProperties = [];
    private string $authorization = "";

    public function generateMessageId(?string $messageId = null): self
    {
        helper('text');

        $messageId = ($messageId) ? $messageId : random_string('alnum', 32);
        $messageId = defined('MESSAGEID') ? MESSAGEID : $messageId;
        $this->brokerProperties['MessageId'] = $messageId;

        return $this;
    }

    public function getMessageId(): string
    {
        return $this->brokerProperties['MessageId'];
    }

    public function setLabel(string $label): self
    {
        $this->brokerProperties['Label'] = $label;

        return $this;
    }

    public function schedule(DateTime $datetime)
    {
        $this->brokerProperties['ScheduledEnqueueTimeUtc'] = $datetime->setTimezone(new DateTimeZone('UTC'));
    }

    public function generateSasToken($uri, $sasKeyName, $sasKeyValue): self
    {
        $expires = defined('MOCK_TIME') ? MOCK_TIME : time();

        $targetUri = strtolower(rawurlencode(strtolower($uri)));
        $week = 60*60*24*7;
        $expires = $expires + $week;
        $toSign = $targetUri . "\n" . $expires;
        $signature = rawurlencode(base64_encode(hash_hmac(
            'sha256',
            $toSign,
            $sasKeyValue,
            true
        )));


        $this->authorization = "SharedAccessSignature sr=" . $targetUri . "&sig=" . $signature . "&se=" . $expires .         "&skn=" . $sasKeyName;

        return $this;
    }

    public function getHeaders(): array
    {
        return [
            'Authorization' => $this->authorization,
            'BrokerProperties' => json_encode($this->brokerProperties)
        ];
    }
}
