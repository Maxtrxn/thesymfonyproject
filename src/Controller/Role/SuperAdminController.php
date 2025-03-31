<?php
namespace App\Controller\Role;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/superadmin', name: 'superadmin')]
class SuperAdminController extends AbstractController
{
    #[Route('', name: '_home')]
    public function index(): Response
    {
        return $this->render('admin/superadmin.html.twig');
    }

    #[Route('/admintable', name: '_admintable')]
    public function userTableAction(EntityManagerInterface $em): Response
    {
        // récupérer tous les produits
        $user = $em->getRepository(User::class)->findAll();

        return $this->render('admin/admintable.html.twig',['user' => $user],);
    }
}
