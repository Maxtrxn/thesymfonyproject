<?php
namespace App\Controller\Role;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_home')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig');
    }
}

