<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo',TextType::class,[
                'label'=>'Pseudo',
                'empty_data' => '',

            ])
            ->add('prenom',TextType::class,[
                'label'=>'Prénom',
                'empty_data' => '',
            ])
            ->add('nom',TextType::class,[
                'label'=>'Nom',
                'empty_data' => '',
            ])
            ->add('telephone',TextType::class,[
                'label'=>'Téléphone',
                'empty_data' => '',
            ])
            ->add('email',EmailType::class,[
                'label'=>'Email',
                'empty_data' => '',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'mapped' => false,
                'required' => false,
            ])

            ->add('campus', EntityType::class, [
                'class'=>Campus::class,
                'choice_label'=>'nom',
                'label'=>'Campus',

            ])
            ->add('photo',FileType::class,[
                'label'=>'Photo',
                'mapped' => false,
                'constraints' => [ new Image( ["mimeTypesMessage" => "Le format de fichier n'est pas autorisé."])
    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
