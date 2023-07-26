<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo',null,[
                'label'=>'Pseudo'
            ])
            ->add('prenom',null,[
                'label'=>'Prénom'
            ])
            ->add('nom',null,[
                'label'=>'Nom'
            ])
            ->add('telephone',null,[
                'label'=>'Téléphone'
            ])
            ->add('email',null,[
                'label'=>'Email'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],

            ])

            ->add('campus', EntityType::class, [
                'class'=>Campus::class,
                'choice_label'=>'nom',
                'label'=>'Campus'
            ])
            ->add('photo',null,[
                'label'=>'Photo'
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
