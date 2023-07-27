<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Fichier CSV',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'text/csv',
                            'application/vnd.ms-excel',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier au format CSV.',
                        'maxSize' => '1M',
                        'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 1 Mo.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
