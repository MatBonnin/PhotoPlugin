<?php

namespace Sylius\Plugin\PhotoPlugin\Form\Type;

use Sylius\Component\Core\Model\AdminUser;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;
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
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => true,
            ])
            ->add('events', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'label' => 'Événements',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Photographer $photographer */
            $photographer = $event->getData();
            $form = $event->getForm();

            // Création de l'AdminUser
            $adminUser = new AdminUser();
            //set local code
            $adminUser->setLocaleCode('fr_FR');
            $adminUser->setEmail($photographer->getEmail());
            $adminUser->setUsername($photographer->getEmail());
            $adminUser->setEnabled(true);

            // Hachage du mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $encodedPassword = $this->passwordHasher->hashPassword($adminUser, $plainPassword);
                $adminUser->setPassword($encodedPassword);
            }

            // Attribuer le rôle ROLE_PHOTOGRAPHER
            $adminUser->addRole('ROLE_PHOTOGRAPHER');

            // Associer l'AdminUser au Photographer
            $photographer->setAdminUser($adminUser);

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
