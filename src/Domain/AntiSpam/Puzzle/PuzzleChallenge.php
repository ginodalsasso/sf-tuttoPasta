<?php

namespace App\Domain\AntiSpam\Puzzle;

use App\Domain\AntiSpam\ChallengeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

// Définition de la classe PuzzleChallenge qui implémente l'interface ChallengeInterface
class PuzzleChallenge implements ChallengeInterface
{
    // Définition de constantes pour les dimensions et autres paramètres
    public const WIDTH = 350;
    public const HEIGHT = 200;
    public const PIECE_WIDTH = 80;
    public const PIECE_HEIGHT = 50;
    private const SESSION_KEY = 'puzzles';
    private const PRECISION = 4;


    // Constructeur pour injecter l'objet RequestStack en lecture seule
    public function __construct(private readonly RequestStack $requestStack)
    {
    }


    // Génère une clé unique pour un nouveau puzzle
    public function generateKey(): string
    {
        // Récupère la session actuelle
        $session = $this->getSession();
        $now = time();
        // Génère des positions aléatoires pour le puzzle
        $x = mt_rand(0, self::WIDTH - self::PIECE_WIDTH);
        $y = mt_rand(0, self::HEIGHT - self::PIECE_HEIGHT);
        // Récupère les puzzles existants dans la session
        $puzzles = $session->get(self::SESSION_KEY, []);
        // Ajoute le nouveau puzzle avec sa clé et sa solution
        $puzzles[] = ['key' => $now, 'solution' => [$x, $y]];
        // Garde uniquement les 10 derniers puzzles dans la session
        $session->set(self::SESSION_KEY, array_slice($puzzles, -10));
        // Retourne la clé générée
        return $now;
    }


    // Vérifie si la réponse fournie pour une clé donnée est correcte
    public function verify(string $key, string $anwser): bool
    {
        // Récupère la solution attendue pour la clé donnée
        $expected = $this->getSolution($key);

        if (!$expected) {
            return false;
        }

        // Retire le puzzle de la session une fois vérifié
        $session = $this->getSession();
        $puzzles = $session->get(self::SESSION_KEY);
        $session->set(self::SESSION_KEY, array_filter($puzzles, fn (array $puzzle) => $puzzle['key'] !== intval($key)));

        // Convertit la réponse fournie en positions x et y
        $got = $this->stringToPosition($anwser);
        // Vérifie si la réponse est proche de la solution attendue dans une certaine précision
        return abs($expected[0] - $got[0]) <= self::PRECISION && abs($expected[1] - $got[1]) <= self::PRECISION;
    }


    /**
     * Récupère la solution pour une clé donnée
     * @return int[]|null
     */
    public function getSolution(string $key): array | null
    {
        // Récupère les puzzles dans la session
        $puzzles = $this->getSession()->get(self::SESSION_KEY, []);
        // Parcourt les puzzles pour trouver la solution correspondant à la clé
        foreach($puzzles as $puzzle) {
            if ($puzzle['key'] !== intval($key)) {
                continue;
            }
            return $puzzle['solution'];
        }
        return null;
    }


    // Récupère la session actuelle à partir de la pile de requêtes
    private function getSession(): SessionInterface
    {
        return $this->requestStack->getMainRequest()->getSession();
    }

    
    /**
     * Convertit une chaîne de caractères en position x et y
     * @return int[]
     */
    private function stringToPosition(string $s): array
    {
        // Sépare la chaîne en deux parties basées sur le caractère '-'
        $parts = explode('-', $s, 2);
        if (count($parts) !== 2) {
            return [-1, -1];
        }
        return [intval($parts[0]), intval($parts[1])];
    }
}
