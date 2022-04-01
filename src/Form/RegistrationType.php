<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class,
        [
            'label' => 'Votre email *',
            'constraints' => [
                new NotBlank([
                    'message' => "Merci d'entrer une adresse mail valide, cette dernière sera nécéssaire pour vous connecter",
                ])
                ]
        ])
        ->add('password', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'label' => 'Choisir un mot de passe *',
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrez un mot de passe valide',
                ]),
                new Length([
                    'min' => 4,
                    'minMessage' => 'Votre mot de passe doit contenir un minimum de 4 caractères',
                    // max length allowed by Symfony for security reasons
                    'max' => 30,
                    'maxMessage' => 'Votre mot de passe doit contenir un maximum de 30 caractères'
                ]),
            ],
        ])
        ->add('firstname', TextType::class, [
            'label' => 'Votre prénom *',
            'required' => true,
        ])
        ->add('lastname', TextType::class, [
            'label' => 'Votre nom *',
            'required' => true,
        ])
        ->add('username', TextType::class, [
            'label' => 'Choisir un pseudo *',
            'required' => true,
        ])
            ->add('bio' , TextareaType::class , [
                'label' => 'Votre description *',
                'attr' => [

                    'placeholder' => 'Parlez de vous',

                ],
                'required' => true,
            ])
            ->add('picture', FileType::class, [
                'label' => 'Votre photo de profil',
                'required' => false ,
            ]  )
            ->add('submit', SubmitType::class , [
                "label" => "Envoyer"
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
