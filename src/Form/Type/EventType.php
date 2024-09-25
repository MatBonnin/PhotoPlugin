<?php

namespace Sylius\Plugin\PhotoPlugin\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'événement',
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date de l\'événement',
                'widget' => 'single_text', // Utilise un widget simple pour faciliter la saisie
            ])
            ->add('photographers', EntityType::class, [ // Changement ici
                'class' => Photographer::class,
                'choice_label' => 'name',
                'label' => 'Photographes',
                'multiple' => true, // Permet de sélectionner plusieurs photographes
                'expanded' => false, // Utilise un sélecteur déroulant (changez en `true` pour des cases à cocher)
                'placeholder' => 'Sélectionnez des photographes',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class, // Spécifie que ce formulaire est lié à l'entité Event
        ]);
    }
}
