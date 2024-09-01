<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class,[
                'label' => "Pseudo",
                'attr' => [
                    'class' => 'data'
                ],

            ])


            ->add('email', EmailType::class,[
                'attr' => [
                    'class' => 'data',
                    'placeholder' => "email@exemple.com",
                ],
            ])


            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les termes et conditions',
                'mapped' => false,
                'attr' => [
                    'class' => 'data agree_terms'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
            ])


            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-group data'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe !',
                    ]),                    
                    new Length([
                        'min' => 12,
                        'max' => 256,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Votre mot de passe ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{13,}$/',
                        'message' => 'Votre mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, un caractère spécial et au moins 13 caractères.'
                    ]),
                ],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez votre mot de passe']
            ])
            
            //HoneyPot
            ->add('firstname', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'style' => 'display:none',
                    'autocomplete' => 'off',
                ],
                'label' => false,
            ])

            
            ->add('captcha', CaptchaType::class, [
                'mapped' => false,
                'route' => 'captcha'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
