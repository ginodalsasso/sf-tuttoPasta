<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use SoftCreatR\MistralAI\MistralAI;

class MistralService
{
    private $mistral;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $mistralApiKey = $parameterBag->get('MISTRAL_API_KEY'); // Récupérer la clé API depuis les paramètres

        $httpClient = new GuzzleClient(); 
        $psr17Factory = new Psr17Factory();

        // Créer l'instance de MistralAI avec les fabriques et le client HTTP
        $this->mistral = new MistralAI(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $httpClient,
            $mistralApiKey
        );
    }

    public function getResponse(string $message): string
    {
        // Appel à l'API Mistral pour créer une complétion de chat
        $response = $this->mistral->createChatCompletion([
            'model' => 'mistral-tiny',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $message
                ],
            ],
        ]);

        // Traitement de la réponse API, si le code de statut est 200, on retourne le contenu du message
        if ($response->getStatusCode() === 200) {
            // Récupère le contenu de la réponse
            $responseObj = json_decode($response->getBody()->getContents(), true);
            return $responseObj['choices'][0]['message']['content'] ?? 'Pas de réponse'; // Je parcours la réponse pour récupérer le contenu du message
        } else {
            return "Error: " . $response->getStatusCode();
        }
    }
}
