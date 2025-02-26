<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ModifierAnnonceType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, ['attr' => ['class'=> 'form-control'], 'label_attr' => ['class'=>
        'fw-bold']])
        ->add('type', TextType::class, ['attr' => ['class'=> 'form-control'], 'label_attr' => ['class'=>
        'fw-bold']])
        ->add('description', TextareaType::class, ['attr' => ['class'=> 'form-control'], 'label_attr' => ['class'=>
        'fw-bold']])
        ->add('prix', TextType::class, ['attr' => ['class'=> 'form-control'], 'label_attr' => ['class'=>
        'fw-bold']])
        ->add('photo', FileType::class, [
            'label' => 'Image ',
            'required' => false,
            'mapped' => false, // Ce champ n'est pas lié directement à l'entité
        ])         
        ->add('modifier', SubmitType::class, ['attr' => ['class'=> 'btn bg-primary text-white m-4' ],
        'row_attr' => ['class' => 'text-center'],])
         ;    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
