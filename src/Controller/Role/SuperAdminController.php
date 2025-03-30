<?php

namespace App\Controller\Role;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SuperAdminController extends AbstractController
{
    #[Route('/super/admin', name: 'app_super_admin')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SuperAdminController.php',
        ]);
    }
}
