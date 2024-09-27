<?php

namespace Sylius\Plugin\PhotoPlugin\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PhotographerType extends AbstractType
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du photographe',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // On ne mappe pas directement le champ mot de passe sur l'entité
            ])
            ->add('events', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'label' => 'Événements',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false, // Important pour appeler les méthodes add/remove
            ]);

        // Ajoutez cet écouteur d'événement pour encoder le mot de passe et synchroniser les relations
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Photographer $photographer */
            $photographer = $event->getData();
            $form = $event->getForm();

            // Gestion du mot de passe
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $encodedPassword = $this->passwordHasher->hashPassword($photographer, $plainPassword);
                $photographer->setPassword($encodedPassword);
            }

            // Synchronisation des relations avec les événements
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
