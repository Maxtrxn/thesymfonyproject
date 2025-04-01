<?php
namespace App\Controller\Role;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserEditType;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/edit/{id}', name: '_edit_user')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserEditType::class, $user,['show_superadmin' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('admin_usertable');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: '_delete_user', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();
        $this->addFlash('danger', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_usertable');
    }

}

