<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomProduit')
            ->add('description')
            ->add('prix')
            //->add('disponible')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                "choice_label" => 'nomCategorie',
            ])
            ->add('user_id', HiddenType::class, [
                'data' => $options['user']->getId(),
            ])
            ->add('Valider', SubmitType::class)
        ;
    }

    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
        $resolver->setRequired('user');
    }
}
