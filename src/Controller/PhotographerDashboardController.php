<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product\Product;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;
use Sylius\Component\Core\Model\AdminUser;

class PhotographerDashboardController extends AbstractController
{
    /**
     * Affiche le tableau de bord des photographes avec les photos uploadées.
     *
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        /** @var AdminUser $adminUser */
        $adminUser = $this->getUser();

        if (!$adminUser instanceof AdminUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant qu\'administrateur.');
        }

        // Récupérer l'entité Photographer associée à l'AdminUser
        /** @var Photographer|null $photographer */
        $photographer = $em->getRepository(Photographer::class)->findOneBy(['adminUser' => $adminUser]);

        if (!$photographer) {
            throw $this->createNotFoundException('Aucun photographe associé à cet utilisateur.');
        }

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
