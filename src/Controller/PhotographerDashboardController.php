<?php

namespace Sylius\Plugin\PhotoPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PhotographerDashboardController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('@PhotoPlugin/photographer/dashboard.html.twig');
    }
}
