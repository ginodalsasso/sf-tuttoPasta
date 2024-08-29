<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Appointment;
use App\Form\Type\CaptchaType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('name', TextType::class, [
                'label' => "Nom",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom ne peut pas être vide.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
            ])


            ->add('firstName', TextType::class, [
                'label' => "Prénom",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prénom ne peut pas être vide.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères.',
                    ]),
                ],
            ])


            ->add('email', EmailType::class,[
                'label' => "E-mail",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data',
                    'placeholder' => "email@exemple.com",
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'e-mail ne peut pas être vide.',
                    ]),
                    new Email([
                        'message' => 'L\'e-mail "{{ value }}" n\'est pas un e-mail valide.',
                    ]),
                ],
            ])


            ->add('message', TextareaType::class, [
                'label' => "Notes supplémentaires",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    "class" => "data",
                    'placeholder' => "Veuillez saisir votre message ici...",

                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le message ne peut pas être vide.',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])


            ->add('startDate', DateType::class, [
                'label' => "Séléctionnez une date",
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'data flatpickr',
                    'placeholder' => "Cliquez ici pour séléctionner une date",
                ],
                'constraints'=>[
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Veuillez séléctionner une date dans le présent !',
                    ]),
                ],
            ])


            ->add('services', EntityType::class, [
                'class' => Service::class,
                'label' => "Séléctionnez un ou plusieurs services",
                'choice_label' => 'serviceName', 
                'multiple' => true,
                'expanded' => true, // true pour checkboxes
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.category IS NOT NULL');
                },
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


            // ->add('captcha', CaptchaType::class, [
            //     'mapped' => false,
            //     'route' => 'captcha'
            // ])


            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
            ])        
        ;
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class, // Permet de lier le formulaire à l'entité Appointment
        ]);
    }
}
