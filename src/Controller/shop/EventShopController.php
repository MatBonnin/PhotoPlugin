<?php

namespace Sylius\Plugin\PhotoPlugin\Controller\shop;

use App\Entity\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class EventShopController extends AbstractController
{
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        // Récupérer l'événement à partir du slug
        $eventRepository = $em->getRepository(Event::class);
        $event = $eventRepository->findOneBy(['slug' => $slug]);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        // Récupérer les produits (photos) associés à l'événement
        $productRepository = $em->getRepository(Product::class);

        $products = $productRepository->createQueryBuilder('p')
            ->leftJoin('p.images', 'i')
            ->addSelect('i')
            ->where('p.event = :event')
            ->andWhere('p.enabled = :enabled')
            ->setParameter('event', $event)
            ->setParameter('enabled', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Rendre la vue avec les données
        return $this->render('@PhotoPlugin/event/show.html.twig', [
            'event' => $event,
            'products' => $products,
        ]);
    }


    public function list(EntityManagerInterface $em): Response
    {
        $eventRepository = $em->getRepository(Event::class);
        $events = $eventRepository->findBy([], ['startDate' => 'DESC']);

        return $this->render('@PhotoPlugin/event/list.html.twig', [
            'events' => $events,
        ]);
    }
}
