<?php

namespace Sylius\Plugin\PhotoPlugin\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Cocur\Slugify\Slugify;

class EventSlugListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Event) {
            return;
        }

        $slugify = new Slugify();
        $slug = $slugify->slugify($entity->getName());

        // Vérifier si le slug existe déjà dans la base de données
        $repository = $args->getEntityManager()->getRepository(Event::class);
        $existingEvent = $repository->findOneBy(['slug' => $slug]);

        $counter = 1;
        $originalSlug = $slug;
        while ($existingEvent) {
            $slug = $originalSlug . '-' . $counter;
            $existingEvent = $repository->findOneBy(['slug' => $slug]);
            $counter++;
        }

        $entity->setSlug($slug);
    }
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->prePersist($args);
    }
}
