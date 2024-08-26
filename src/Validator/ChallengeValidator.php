<?php

namespace App\Validator;

use App\Domain\AntiSpam\ChallengeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ChallengeValidator extends ConstraintValidator
{

    public function __construct(private readonly ChallengeInterface $challenge){

    }

    /**
     * Valide une valeur par rapport à une contrainte
     * @param array{challenge: string, answer: string} $value
     */
    public function validate($value, Constraint $constraint)
    {
        // Si la valeur est nulle ou vide, ne pas valider
        if (null === $value || '' === $value) {
            return;
        }

        // Vérifie si la réponse au challenge est correcte en utilisant l'objet challenge
        if (!$this->challenge->verify($value['challenge'], $value['answer'] ?? '')) { 
            // Si la vérification échoue, construit et ajoute une violation à la contrainte
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
