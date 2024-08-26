<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactFormType extends AbstractType
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


            ->add('email', EmailType::class,[
                'label' => "E-mail",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'data'
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


            ->add('subject', TextType::class, [
                'label' => "Sujet",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    "class" => "data"
                ],
            ])


            ->add('message', TextareaType::class, [
                'label' => "Message",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    "class" => "data"
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le message ne peut pas être vide.',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le message doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
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

            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
            ])     
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
