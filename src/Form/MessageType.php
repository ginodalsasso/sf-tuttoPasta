<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Message;
use Dompdf\FrameDecorator\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'class' => 'data'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'class' => 'data',
                    'placeholder' => "Veuillez saisir votre message ici...",
                ]
            ])
            ->add('recipient', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])
            ->add("submit", SubmitType::class, [
                'attr' => [
                    'class' => 'full_button_black'
                    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
