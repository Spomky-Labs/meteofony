<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\PasswordStrength;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => [
                    'autocomplete' => 'username',
                ],
                'label' => 'Votre nom d’utilisateur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez votre nom d’utilisateur',
                    ])
                ],
            ])
            ->add('captcha', CaptchaType::class, [
                'label' => false,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
