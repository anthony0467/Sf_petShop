<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Range;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('note', NumberType::class, [
            'constraints' => [
                new Range([
                    'min' => 1,
                    'max' => 5,
                    'minMessage' => 'La note doit être d\'au moins {{ limit }}',
                    'maxMessage' => 'La note ne peut pas dépasser {{ limit }}'
                ])
            ]
        ])
            ->add('commentaire', TextareaType::class)
            ->add('parentid', HiddenType::class, [
                'mapped' =>false,
            ])
            ->add('Envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
