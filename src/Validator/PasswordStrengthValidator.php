<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use ZxcvbnPhp\Matchers\DictionaryMatch;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Zxcvbn;

class PasswordStrengthValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordStrength) {
            throw new UnexpectedTypeException($constraint, PasswordStrength::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $zxcvbn = new Zxcvbn();
        $weak = $zxcvbn->passwordStrength($value, $constraint->userData);

        if ($weak['score'] < $constraint->score) {
            $this->context->buildViolation($constraint->lowPasswordMessage)->addViolation();
        }

        $wordList = $this->containsUserInputs($weak['sequence'] ?? []);

        if (count($wordList) !== 0) {
            $this->context->buildViolation($constraint->forbiddenWordMessage, ['{{ wordList }}' => implode(', ', $wordList)])->addViolation();
        }
    }

    /**
     * @param array<MatchInterface> $sequence
     */
    private function containsUserInputs(array $sequence): array
    {
        $wordList = [];

        foreach ($sequence as $item) {
            if (! $item instanceof DictionaryMatch) {
                continue;
            }
            if ($item->dictionaryName === 'user_inputs') {
                $wordList[] = $item->token;
            }
        }

        return $wordList;
    }
}
