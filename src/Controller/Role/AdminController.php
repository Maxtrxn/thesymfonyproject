<?php
namespace App\Controller\Role;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    #[Route('', name: '_home')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/usertable', name: '_usertable')]
    public function userTableAction(EntityManagerInterface $em): Response
    {
        // récupérer tous les produits
        $user = $em->getRepository(User::class)->findAll();

        return $this->render('admin/usertable.html.twig',['user' => $user],);
    }


}

