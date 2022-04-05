<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
            ->add('content', TextareaType::class,[
                'label'=> false,
                'attr'=>[
                    'placeholder'=> "Quoi de neuf?"
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer du contenu',
                    ]),
                    new Length([
                        'max' => 140,
                        'maxMessage' => 'Vous avez dépassé la taille limite de caractères'
                    ]),
            ]])
            ->add('picture', FileType::class,[
                'label'=> false,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
