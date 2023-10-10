<?php

namespace App\Form;

use App\Entity\Offre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
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
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'Le prix attendu doit être supérieur à zéro.',
                    ]),
                ]
            ])
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
