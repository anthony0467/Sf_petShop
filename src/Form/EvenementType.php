<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Titre', TextType::class, [
                'constraints' => [
                    new Length([
                        'max' => 150,
                        'maxMessage' => 'Le champ ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('localisation', TextType::class, [
                'attr' => ['placeholder' => 'Saisissez votre adresse ici'],
                'constraints' => [
                    new Length([
                        'max' => 150,
                        'maxMessage' => 'Le champ ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('ville', TextType::class, [
                'attr' => ['placeholder' => 'ex: Marseille'],
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Le champ ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],

            ])
            ->add('cp', TextType::class, [
                'attr' => ['placeholder' => 'ex : 13000'],
            ])
            ->add('images', FileType::class, [
                'label' => 'Ajoute des photos',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Count([
                        'max' => 2,
                        'maxMessage' => 'Vous ne pouvez télécharger que {{ limit }} images maximum',
                    ]),
                    new All([
                        new File([
                            'maxSize' => '2048k',
                            'maxSizeMessage' => 'La taille maximale autorisée est de {{ limit }}.',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png'
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF)',
                        ])
                    ])
                ],
                'label_attr' => ['class' => 'file-label'],
            ])
            ->add('description', TextareaType::class)
            ->add('dateEvenement',  DateType::class, [
                'widget' => 'single_text' // calendrier
            ])
            ->add('lien', UrlType::class, [
                'label' => 'URL(facultatif)',
                'required' => false,
                'constraints' => [
                    new Url(),
                ],
            ])
            ->add('Ajouter', SubmitType::class, [
                'attr' => ['class' => 'btn']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'attr' => [
                'id' => 'evenement_form',
            ],
        ]);
    }
}
