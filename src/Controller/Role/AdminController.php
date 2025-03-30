<?php

namespace App\Controller\Role;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')] // Ce préfixe s’applique à toutes les méthodes de ce contrôleur
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_home')] // Cela rend accessible /admin (page d’accueil admin)
    public function index(): Response
    {
        return new Response('<h1>Bienvenue dans l’espace super-admin</h1>');
    }
}
