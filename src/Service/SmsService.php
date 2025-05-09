<?php

namespace App\Service;

use Twilio\Rest\Client;

class SmsService
{
    private string $twilioSid;
    private string $twilioToken;
    private string $twilioFrom;

    public function __construct(string $twilioSid, string $twilioToken, string $twilioFrom)
    {
        $this->twilioSid = $twilioSid;
        $this->twilioToken = $twilioToken;
        $this->twilioFrom = $twilioFrom;
    }

    public function send(string $to, string $message): void
    {
        $client = new Client($this->twilioSid, $this->twilioToken);
        $client->messages->create($to, [
            'from' => $this->twilioFrom,
            'body' => $message
        ]);
    }
}