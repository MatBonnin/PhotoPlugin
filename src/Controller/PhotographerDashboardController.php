<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product\Product;

class PhotographerDashboardController extends AbstractController
{
    public function index(EntityManagerInterface $em): Response
    {
        $photographer = $this->getUser();

        // Récupérer tous les produits (photos) uploadés par le photographe
        $productRepository = $em->getRepository(Product::class);

        $products = $productRepository->createQueryBuilder('p')
            ->leftJoin('p.event', 'e')
            ->addSelect('e')
            ->leftJoin('p.images', 'i')
            ->addSelect('i')
            ->where('p.photographer = :photographer')
            ->setParameter('photographer', $photographer)
            ->orderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        // Grouper les produits par événement
        $events = [];

        foreach ($products as $product) {
            $event = $product->getEvent();
            if ($event) {
                $eventId = $event->getId();
                if (!isset($events[$eventId])) {
                    $events[$eventId] = [
                        'event' => $event,
                        'products' => [],
                    ];
                }
                $events[$eventId]['products'][] = $product;
            }
        }

        return $this->render('@PhotoPlugin/photographer/dashboard.html.twig', [
            'events' => $events,
        ]);
    }
}
