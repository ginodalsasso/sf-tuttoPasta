<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsGenerator
{
    public function sendSms($message)
    {
        // Si la variable d'environnement n'est pas définie, on utilise null
        $sid = $_ENV['TWILIO_SID'] ?? null; 
        $authToken = $_ENV['TWILIO_TOKEN'] ?? null;
        $from = $_ENV['TWILIO_FROM'] ?? null;
        $toNumber = $_ENV['TWILIO_TO'];

        if (!$sid || !$authToken || !$from || !$toNumber) {
            die("Les variables d'environnement ne sont pas définies correctement.");
        }
        
        $client = new Client($sid, $authToken);
        $client->messages->create(
            $toNumber,
            [
                'from' => $from,
                'body' => $message
            ]
        );
    }
}