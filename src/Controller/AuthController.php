<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/auth', name: 'auth')]
final class AuthController extends AbstractController
{
    #[Route('', name: '')]
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('accueil');
        }
        return $this->render('auth/index.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/new', name: 'auth_new')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('accueil'); // ou autre page comme 'profile'
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $user->setRoles(['ROLE_USER']);

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Bienvenue, ' . $user->getUsername() . ' !');
                return $this->redirectToRoute('accueil');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Ce nom d\'utilisateur est déjà utilisé.');
            }
        }

        return $this->render('auth/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('accueil');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
