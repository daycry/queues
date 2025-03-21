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

namespace Daycry\Queues\Libraries;

use CodeIgniter\I18n\Time;
use DateTime;
use DateTimeZone;

class ServiceBusHeaders
{
    private array $brokerProperties = [];
    private string $authorization   = '';

    public function generateMessageId(?string $messageId = null): self
    {
        helper('text');

        $messageId                           = ($messageId) ?: random_string('alnum', 32);
        $messageId                           = (getenv('MESSAGEID')) ?: $messageId;
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

    public function schedule(DateTime $datetime): self
    {
        $this->brokerProperties['ScheduledEnqueueTimeUtc'] = $datetime->setTimezone(new DateTimeZone('UTC'))->getTimestamp();

        return $this;
    }

    public function generateSasToken($uri, $sasKeyName, $sasKeyValue): self
    {
        $expires = (getenv('MOCK_TIME')) ? Time::createFromTimestamp((int) getenv('MOCK_TIME')) : Time::now();

        $targetUri = strtolower(rawurlencode(strtolower($uri)));
        $week      = 60 * 60 * 24 * 7;
        $expires   = $expires->getTimestamp() + $week;
        $toSign    = $targetUri . "\n" . $expires;
        $signature = rawurlencode(base64_encode(hash_hmac(
            'sha256',
            $toSign,
            $sasKeyValue,
            true,
        )));

        $this->authorization = 'SharedAccessSignature sr=' . $targetUri . '&sig=' . $signature . '&se=' . $expires . '&skn=' . $sasKeyName;

        return $this;
    }

    public function getHeaders(): array
    {
        return [
            'Authorization'    => $this->authorization,
            'BrokerProperties' => json_encode($this->brokerProperties),
        ];
    }
}
