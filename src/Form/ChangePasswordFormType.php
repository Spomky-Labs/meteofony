<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userData = $options['userData'] ?? [];
        dump($userData);
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
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Votre mot de passe doit avoir au moins {{ limit }} charactères',
                            // max length allowed by Symfony for security reasons
                            'max' => 64,
                            'maxMessage' => 'Le maximum de caractères autorisé est de {{ limit }} caractères',
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
            ->setAllowedTypes('userData', 'string[]')
        ;
    }
}
