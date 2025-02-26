<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'], 
                'label_attr' => ['class' => 'fw-bold']
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Prestation de serice' => 'prestation de service',
                    'Location' => 'location',
                ],
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'fw-bold'],
                'placeholder' => 'Sélectionnez un type', // Optionnel, ajoute une valeur vide par défaut
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control'], 
                'label_attr' => ['class' => 'fw-bold']
            ])
            ->add('prix', TextType::class, [
                'attr' => ['class' => 'form-control'], 
                'label_attr' => ['class' => 'fw-bold']
            ])
            ->add('photo', FileType::class, [
                'label' => 'Image (JPG, PNG, etc.)',
                'required' => false,
                'mapped' => false, 
                'attr' => ['class' => 'form-control', 'id' => 'photoField'], // Ajoute un ID unique
            ])
            ->add('envoyer', SubmitType::class, [
                'attr' => ['class' => 'btn bg-primary text-white m-4'],
                'row_attr' => ['class' => 'text-center'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
