<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'Photos'
            ])
            ->add('nomProduit', TextType::class, [
                'label' => 'Nom du produit'
            ])
            ->add('description', TextareaType::class)
            ->add('prix', NumberType::class)
            ->add('disponible', NumberType::class,  [
                'attr' => [
                    'min' => 1 // Valeur minimale,
                    
                ]])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                "choice_label" => 'nomCategorie',
            ])
            ->add('Valider', SubmitType::class)
            
        ;
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
