<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['only_motif']) {
            $builder->add('motif')
                ->add('creer', SubmitType::class, [
                    'label' => 'Créer'
                ]);
        } else {
            $builder
                ->add('nom', null, [
                    'label' => 'Nom de la sortie :',
                    //'help' => 'Le nom doit être composé de 3 à 100 caractères'
                ])
                ->add('dateDebut', null, [
                    'label' => 'Date et heure de la sortie :',
                    'widget' => 'single_text',
                    'required' => false,
                ])
                ->add('dateLimiteInscription', null, [
                    'label' => 'Date de clôture des inscriptions :',
                    'widget' => 'single_text',
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
                    'choice_label' => 'nom',
                    'disabled' => true
                ])
                ->add('ville', EntityType::class, [
                    'label' => 'Ville :',
                    'placeholder' => 'Sélectionner une ville',
                    'class' => Ville::class,
                    'choice_label' => 'nom',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('v')
                            ->orderBy('v.nom', 'ASC');
                    },
                    'mapped' => false,
                    'required' => false
                ])

                //Affichage par défaut du formulaire de Lieu, avant toute action sur le formulaire Ville
                //Désactivé par défaut
                ->add('lieu', EntityType::class, [
                    //'label' => 'Lieu :',
                    'placeholder' => 'Sélectionner un lieu',
                    'class' => Lieu::class,
                    //'disabled' => true,
                ])
                ->add('creer', SubmitType::class, [
                    'label' => 'Créer'
                ]);

                if ($options['publication_false']) {
                    $builder
                        ->add('publier', SubmitType::class, [
                            'label' => 'Publier'
                        ])
                        ->add('supprimer', SubmitType::class, [
                            'label' => 'Supprimer'
                        ]);
                }

                //Dynamisme des sélecteurs
                $formModifier = function (FormInterface $form, Ville $ville = null) {
                    $lieux = $ville === null ? [] : $ville->getLieux();

                    $form->add('lieu', EntityType::class, [
                        'class' => Lieu::class,
                        'choice_label' => 'nom',
                        'placeholder' => 'Sélectionnez un lieu',
                        'choices' => $lieux
                    ]);
                };

                //Ecoute de la soumission sur le formulaire Ville.
                //On passe le résultat à $formModifier
                $builder->get('ville')->addEventListener(
                    FormEvents::POST_SUBMIT,
                    function (FormEvent $event) use ($formModifier) {
                        $ville = $event->getForm()->getData();
                        $parent = $event->getForm()->getParent();
                        $formModifier($parent, $ville);
                    }
                );

                //Ecoute de la soumission sur le formulaire Ville.
                //On passe le résultat à $formModifier
                $builder->get('ville')->addEventListener(
                    FormEvents::PRE_SET_DATA,
                    function (FormEvent $event) use ($formModifier) {
                        $ville = $event->getData();
                        $parent = $event->getForm()->getParent();
                        $formModifier($parent, $ville);
                    }
                );

                $builder->get('lieu')->addEventListener(
                    FormEvents::POST_SUBMIT,
                    function (FormEvent $event) {
                        $event->getForm()->getData();
                        $event->getForm()->getParent();

                    }
                );

                //Ajout d'action pour Javascript mais peut-être présent par défaut ?
                $builder->setAction($options['action']);
            }
        }


        public
        function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Sortie::class,
                //'lieu_class' => Lieu::class,
                'only_motif' => false,
                'publication_false' => true,
            ]);
        }
    }