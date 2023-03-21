<?php
namespace App\Mail;

use Swift_Mime_Message;
use Illuminate\Mail\Transport\SparkPostTransport;

class SparkPostEUTransport extends SparkPostTransport {
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $recipients = $this->getRecipients($message);

        $message->setBcc([]);

        $response = $this->client->post('https://api.eu.sparkpost.com/api/v1/transmissions', [
            'headers' => [
                'Authorization' => $this->key,
            ],
            'json' => array_merge([
                'recipients' => $recipients,
                'content' => [
                    'email_rfc822' => $message->toString(),
                ],
            ], $this->options),
        ]);

        $message->getHeaders()->addTextHeader(
            'X-SparkPost-Transmission-ID', $this->getTransmissionId($response)
        );

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }
}

