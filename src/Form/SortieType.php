<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\Event;

class SortieType extends AbstractType
{
    private LieuRepository $lieuRepository;

    public function __construct(LieuRepository $lieuRepository){
        $this->lieuRepository = $lieuRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie :',
                //'help' => 'Le nom doit être composé de 3 à 100 caractères'
            ])
            ->add('dateDebut', null, [
                'label' => 'Date et heure de la sortie :',
                'widget' => 'single_text',
                'required' => false,
                //'html5' => false,
                //'format' => 'dd/MM/yyyyy HH:mm'
            ])
            ->add('dateLimiteInscription', null, [
                'label' => 'Date de clôture des inscriptions :',
                //'format' => 'dd/MM/yyyyy',
                //erreur Cannot use the "format" option of "Symfony\Component\Form\Extension\Core\Type\DateTimeType" when the "html5" option is enabled.
                //'html5' => false,
                'widget' => 'single_text',
                //'data' => 'tomorrow',
                //'help' => 'La date de clôture ne peut être supérieure à la date de sortie'
            ])
            ->add('nbInscritptionMax', IntegerType::class, [
                'label' => 'Nombre de places :',
                //'data"' => '1'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée en minutes :',
                //'data' => '15',
                //'help' => 'Une sortie doit être au minimum de 15 minutes'
            ])
            ->add('description')
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Ville :',
                'placeholder' => 'Sélectionner une ville',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false,
                'required' => false
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu :',
                'placeholder' => 'Sélectionner un lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ]);
    }


        //DEBUT DU MERDIER DEV SUR LA DEPENDANCE DE FORMULAIRES
/*
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    //$data = $event->getData();
                    $ville = $event->getData()['ville'] ?? null;
                    $lieux = null === $ville ? [] : $ville->getLieux();
                    $form->add('lieu', EntityType::class, [
                        'class' => Lieu::class,
                        'placeholder' => 'Sélectionner une ville',
                        'choices' => $lieux,
                        'choice_label' => 'nom'
                    ]);
                }
            )

//            ->add('Valider', SubmitType::class);

        $formModifier = function (FormInterface $form, Ville $ville = null) {
            $lieux = $ville === null ? [] : $this->lieuRepository->findLieuxParVille($ville);

            $form->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                    'choice_label' => 'nom',
                    'disabled' => $ville === null,
                    'placeholder' => 'Sélectionnez un lieu',
                    'choices' => $lieux
                ]
            );
        };
/*
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                //$ville= $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(),  );
            }
        );

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $ville = $event->getForm()->getData();
                $formModifier($event-getForm()->getParent(), $ville);
            }
        );


    }

/*

            $formModifier = function(FormInterface $form, Ville $ville = null){
              $lieu = null === $ville ? [] : $ville->getLieux();

              $form->add('lieu', EntityType::class, [
                  'class' => Lieu::class,
                  'choices' => $lieu,
                  'choice_label' => 'nom',
                  'placeholder' => 'Sélectionner un lieu',
                  'label' => 'Lieu :'
              ]);
            };

            $builder->get('ville')->addEventListener(
        FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier){
                //$data = $event->getData();
                $ville= $event->getData()['ville'] ?? null;
                dump("La VILLE choisie est " . $ville);
                //$formModifier($event->getForm()->getParent(), $ville);
            }
        );
    }







/*
    private function ajoutListeLieu(FormInterface $form, ?Ville $ville)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder()(
            'lieu',
            EntityType::class,
            null,
            [
                'class' => Lieu::class,
                'placeholder' => $ville ? 'Sélection un lieu' : "Sélection d'abord une ville",
                'choices' => $ville ? $ville->getLieux() : []
            ]
        );
    }
*/

    // FIN DU DEV SUR LA DEPENDANCE DE FORMULAIRE

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
