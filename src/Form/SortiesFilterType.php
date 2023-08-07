<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SortiesFilterType extends AbstractType
{
    private Security $security;
    public function __construct(Security $security){
        $this->security = $security;
    }

     public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $participantConnecte = $this->security->getUser();
        $campusParticipant = $participantConnecte->getCampus();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false
            ])
            ->add('from', DateType::class, [
                'label' => "Entre",
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('to', DateType::class, [
                'label' => "Et",
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'required' => false,
                'data'=> $campusParticipant
            ])

            ->add('organized', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur/trice",
                'required' => false
            ])
            ->add('subscribed', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit/e",
                'required' => false
            ])
            ->add('notSubscribed', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
                'required' => false
            ])
            ->add('over', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchSortie::class,
            'method' => 'GET',
            'csrf_protection' => true,
        ]);
    }

}
