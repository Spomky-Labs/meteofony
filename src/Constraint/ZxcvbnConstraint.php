<?php

declare(strict_types=1);

namespace App\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class ZxcvbnConstraint extends Constraint
{
    public string $message = 'The password is too weak';

    public string $restrictedDataMessage = 'The password contains restricted data';

    /**
     * @var string[]
     */
    public array $userData = [];

    public int $threshold = 3;
}
