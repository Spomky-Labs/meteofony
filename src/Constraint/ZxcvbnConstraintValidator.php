<?php

declare(strict_types=1);

namespace App\Constraint;

use function is_string;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use ZxcvbnPhp\Matchers\DictionaryMatch;
use ZxcvbnPhp\Matchers\MatchInterface;
use ZxcvbnPhp\Zxcvbn;

class ZxcvbnConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (! $constraint instanceof ZxcvbnConstraint) {
            throw new UnexpectedTypeException($constraint, ZxcvbnConstraint::class);
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $zxcvbn = new Zxcvbn();

        $result = $zxcvbn->passwordStrength($value, $constraint->userData);

        if ($result['score'] < $constraint->threshold) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
        if ($this->containsUserInputs($result['sequence'] ?? [])) {
            $this->context->buildViolation($constraint->restrictedDataMessage)
                ->addViolation();
        }
    }

    /**
     * @param array<MatchInterface> $sequence
     */
    private function containsUserInputs(array $sequence): bool
    {
        foreach ($sequence as $item) {
            if (! $item instanceof DictionaryMatch) {
                continue;
            }
            if ($item->dictionaryName === 'user_inputs') {
                return true;
            }
        }

        return false;
    }
}
