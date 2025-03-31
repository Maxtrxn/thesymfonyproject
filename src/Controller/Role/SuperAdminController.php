<?php
namespace App\Controller\Role;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/superadmin')]
class SuperAdminController extends AbstractController
{
    #[Route('', name: 'superadmin_home')]
    public function index(): Response
    {
        return $this->render('admin/superadmin.html.twig');
    }
}
