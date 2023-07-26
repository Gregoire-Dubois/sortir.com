<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie :',
                'help' => 'Le nom doit être composé de 3 à 100 caractères'
            ])
            ->add('dateDebut', null, [
                'label' => 'Date et heure de la sortie :',
                'html5' => false,
                'format' => 'dd/MM/yyyyy HH:mm'
            ])
            ->add('dateLimiteInscription', null, [
                'label' => 'Date de clôture des inscriptions :',
                'format' => 'dd/MM/yyyyy',
                //erreur Cannot use the "format" option of "Symfony\Component\Form\Extension\Core\Type\DateTimeType" when the "html5" option is enabled.
                'html5' => false,
                'widget' => 'single_text',
                //'data' => 'tomorrow',
                'help' => 'La date de clôture ne peut être supérieure à la date de sortie'
            ])
            ->add('nbInscritptionMax', IntegerType::class, [
                'label' => 'Nombre de places :',
                //'data"' => '1'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée en minutes :',
                //'data' => '15',
                'help' => 'Une sortie doit être au minimum de 15 minutes'
            ])
            ->add('description')
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            /*
            ->add('ville', ChoiceType::class, [
                'label' => 'Ville :',
                'choices' => $this->getVillesChoices(),
                //'choice_label' => 'nom'
            ])*/
            ->add('ville', EntityType::class, [
                'label' => 'Ville :',
                'class' => Ville::class,
                'choice_label' => function ($VilleCP) {
                return $VilleCP->getNom();
    },
                'mapped' => false,
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu :',
                'class' => Lieu::class,
                'choice_label' => 'nom'
            ])
        ;
    }

    /*Bout de code qui ne sert pas car ne fonctionne pas
    private function getVillesChoices(): array
    {
        $villes = $this->entityManager->getRepository('App\Entity\Ville')->findAll();
        $choices = [];

        foreach ($villes as $ville) {
            $choices[$ville->getNom()] = $ville->getId();
        }
        return $choices;
    }
    */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
