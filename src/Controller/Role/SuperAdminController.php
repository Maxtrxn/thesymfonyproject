<?php
namespace App\Controller\Role;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/superadmin', name: 'superadmin')]
class SuperAdminController extends AbstractController
{
    #[Route('', name: '_home')]
    public function index(): Response
    {
        return $this->render('admin/superadmin.html.twig');
    }

    #[Route('/usertable', name: '_usertable')]
    public function userTableAction(EntityManagerInterface $em): Response
    {
        $allUsers = $em->getRepository(User::class)->findAll();
        $eligibleUsers = array_filter($allUsers, function(User $u) {
            $roles = $u->getRoles();
            return !in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_SUPER_ADMIN', $roles);
        });

        return $this->render('admin/admintable.html.twig', ['user' => $eligibleUsers]);
    }

    #[Route('/promote/{id}/{token}', name: '_promote_user', methods: ['GET'])]
    public function promoteUser(User $user, string $token, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $this->addFlash('error', "Cet utilisateur est déjà administrateur ou super-administrateur.");
            return $this->redirectToRoute('superadmin_usertable');
        }

        $csrfToken = new CsrfToken('promote'.$user->getId(), $token);
        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $roles = $user->getRoles();
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles(array_unique($roles));
        $em->flush();

        $this->addFlash('success', "L'utilisateur {$user->getUsername()} a été promu administrateur.");
        return $this->redirectToRoute('superadmin_usertable');
    }
}
