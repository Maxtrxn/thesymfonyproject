<?php
namespace App\Controller\Role;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserEditType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    /**
     * Vérifie que l'utilisateur connecté n'est PAS un super-administrateur.
     * Si c'est le cas, l'accès est refusé.
     */
    private function checkNotSuperAdmin(): void
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException("Les super-administrateurs ne peuvent pas accéder à cette section.");
        }
    }

    #[Route('', name: '_home')]
    public function index(): Response
    {
        $this->checkNotSuperAdmin();
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/usertable', name: '_usertable')]
    public function userTableAction(EntityManagerInterface $em): Response
    {
        $this->checkNotSuperAdmin();
        // Récupère tous les utilisateurs
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/usertable.html.twig', ['user' => $users]);
    }
    #[Route('/delete/{id}/{token}', name: '_delete_user', methods: ['GET'])]
    public function deleteUser(User $user, string $token, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $this->checkNotSuperAdmin();

        // Vérifications
        if ($user === $this->getUser()) {
            $this->addFlash('error', "Vous ne pouvez pas supprimer votre propre compte.");
            return $this->redirectToRoute('admin_usertable');
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', "Impossible de supprimer un administrateur.");
            return $this->redirectToRoute('admin_usertable');
        }

        if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('delete'.$user->getId(), $token))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('danger', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_usertable');
    }

}
