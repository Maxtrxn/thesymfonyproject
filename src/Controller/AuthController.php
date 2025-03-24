<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/auth', name: 'auth')]
final class AuthController extends AbstractController
{
    #[Route('', name: '')]
    public function index(): Response
    {
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/new', name: '_new')]
    public function newAction(): Response
    {
        return $this->render('auth/new.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }
}
