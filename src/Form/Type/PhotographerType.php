<?php

namespace Sylius\Plugin\PhotoPlugin\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PhotographerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du photographe',
            ])
            ->add('events', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'label' => 'Événements',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false, // Important pour appeler les méthodes add/remove
            ]);

        // Ajoutez cet écouteur d'événement pour synchroniser les relations
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Photographer $photographer */
            $photographer = $event->getData();
            foreach ($photographer->getEvents() as $eventEntity) {
                if (!$eventEntity->getPhotographers()->contains($photographer)) {
                    $eventEntity->addPhotographer($photographer);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Photographer::class,
        ]);
    }
}
