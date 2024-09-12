<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsGenerator
{
    public function sendSms($message)
    {
        // Si la variable d'environnement n'est pas définie, on utilise null
        $sid = $_ENV['TWILIO_SID'] ?? null; // SID de l'API Twilio
        $authToken = $_ENV['TWILIO_TOKEN'] ?? null; // Token d'authentification
        $from = $_ENV['TWILIO_FROM'] ?? null; // Numéro de téléphone Twilio
        $toNumber = $_ENV['ADMIN_TO']; // Numéro de téléphone de TuttoPasta

        if (!$sid || !$authToken || !$from || !$toNumber) {
            die("Les variables d'environnement ne sont pas définies correctement.");
        }
        
        // Crée une nouvelle instance de Client
        $client = new Client($sid, $authToken);
        $client->messages->create(
            // Numéro de téléphone de destination
            $toNumber,
            [
                'from' => $from, // Numéro de téléphone Twilio
                'body' => $message // Message à envoyer
            ]
        );
    }
}