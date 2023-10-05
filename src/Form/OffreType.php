<?php

namespace App\Form;

use App\Entity\Offre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix', NumberType::class, [
                'scale' => 2, // Définir deux décimales après la virgule
                'attr' => [
                    'placeholder' => 'euros', // Ajouter un placeholder
                ],
                'constraints' => [
                    new Range([
                        'min' => 0.1,
                        'minMessage' => 'Le prix attendu doit être au-dessus de 0.00€ ',

                    ])
                ]
            ])
            //->add('date')
            //->add('statut')
            //->add('produits')
            ->add('Valider', SubmitType::class,  [
                'attr' => [
                    'class' => 'btn'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}
