<?php

namespace App\Form;

use App\Entity\Slider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SliderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('images', FileType::class, [
                'label' => 'Photos',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Count([
                        'max' => 1,
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
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL(facultatif)',
                'required' => false,
                'constraints' => [
                    new Url(),
                ],
            ])
            ->add('nameButton', TextType::class, [
                'label' => 'Nom du bouton(facultatif)',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le champ ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('Valider', SubmitType::class, [
                'attr' => ['class' => 'btn']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Slider::class,
        ]);
    }
}
