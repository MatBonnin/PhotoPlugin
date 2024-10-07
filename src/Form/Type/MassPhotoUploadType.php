<?php

namespace Sylius\Plugin\PhotoPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as DoctrineEntityType;

class MassPhotoUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Champ pour sélectionner l'événement
            ->add('event', DoctrineEntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
            ])
            // Champ pour uploader les fichiers
            ->add('photos', FileType::class, [
                'label' => 'Photos (JPEG, PNG)',
                'multiple' => true,
                'mapped' => false,
                'required' => true,
            ]);
    }
}
