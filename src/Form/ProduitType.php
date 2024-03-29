<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            //mapped false pour ne pas le lier a la base de données
            ->add('images', FileType::class, [
                'label' =>  'Ajoute des photos',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Count([
                        'max' => 4,
                        'maxMessage' => 'Vous ne pouvez télécharger que {{ limit }} images maximum',
                    ]),
                    new All([
                        new File([
                            'maxSize' => '1024k',
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
            ->add('nomProduit', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'ex: croquettes'],
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le champ ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['placeholder' => 'ex: Sac de 5 kilos jamais utilisé', 'rows' => 5, 'cols' => 33]
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix Unitaire',
                'attr' => ['placeholder' => '0.00€'],
                'constraints' => [
                    new Range([
                        'min' => 0.1,
                        'minMessage' => 'Le prix attendu doit être au-dessus de 0.1€ ',

                    ])
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Sélectionner une catégorie' => '', // Libellé par défaut
                    'Neuf' => 'neuf',
                    'Occasion' => 'occasion',
                    'Usagés' => 'usagé',
                ],
                'required' => true,
            ])
            /*->add('disponible', NumberType::class,  [
                'attr' => [
                    'min' => 1 // Valeur minimale,
                    
                ]])*/
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                "choice_label" => 'nomCategorie',
            ])
            ->add('Valider', SubmitType::class);
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
