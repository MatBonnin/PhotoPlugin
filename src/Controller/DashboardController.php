<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class DashboardController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function index(): Response
    {
        // Recherche tous les produits dans la base de données
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        // Rend la vue du tableau de bord avec la liste des produits
        return $this->render('dashboard/index.html.twig', [
            'products' => $products,
        ]);
    }
}
