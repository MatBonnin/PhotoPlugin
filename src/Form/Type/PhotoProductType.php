<?php

namespace Sylius\Plugin\PhotoPlugin\Form\Type;

use Sylius\Plugin\PhotoPlugin\Entity\Product;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType as BaseProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhotoProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom du produit',
            ])
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choices' => $options['events'],
                'choice_label' => 'name',
                'label' => 'Événement',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'events' => [],
        ]);
    }
}
