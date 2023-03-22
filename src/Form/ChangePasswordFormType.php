<?php

declare(strict_types=1);

namespace App\Form;

use App\Constraint\ZxcvbnConstraint;
use function is_array;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use TypeError;

final class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userData = $options['userData'];
        is_array($userData) || throw new TypeError('Expected array');

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Votre mot de passe',
                        ]),
                        new NotCompromisedPassword([
                            'message' => 'Votre mot de passe ne doit pas être compromis',
                        ]),
                        new ZxcvbnConstraint([
                            'message' => 'Votre mot de passe est trop simple',
                            'userInputs' => $userData,
                            'restrictedDataMessage' => 'Votre mot de passe ne doit pas contenir de données personnelles ou en rapport avec le site',
                        ]),
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Votre mot de passe doit avoir au moins {{ limit }} charactères',
                            // max length allowed by Symfony for security reasons
                            'max' => 68,
                            'maxMessage' => 'Votre mot de passe doit avoir au plus {{ limit }} charactères',
                        ]),
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'label' => 'Répétez le nouveau mot de passe',
                ],
                'invalid_message' => 'Le nouveau mot de passe n’a pas été confirmé.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'userData' => [],
        ])
            ->setAllowedTypes('userData', 'string[]');
    }
}
