<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PasswordStrength extends Constraint
{
    public int $score = 2;
    public array $userData = [];
    public string $lowPasswordMessage = 'Le mot de passe est trop faible';
    public string $forbiddenWordMessage = 'Le mot passe contient un ou plusieurs mots non autorisés : {{ wordList }}';

}
