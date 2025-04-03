<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Cart;
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

            // Création d'un panier vide et liaison avec l'utilisateur
            $cart = new Cart();
            // Selon le mapping inversé, l'utilisateur est le propriétaire de la relation
            // On associe donc le panier à l'utilisateur.
            $user->setCart($cart);
            // Si ta méthode setCart() de l'entité User gère aussi la mise à jour du côté Cart (setOwner), c'est parfait.
            // Sinon, n'oublie pas de faire aussi : $cart->setOwner($user);

            try {
                $entityManager->persist($user);
                // Si cascade persist est bien configuré sur la relation, pas besoin de persister manuellement le Cart.
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

        // Récupération d'une éventuelle erreur de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        // Dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}
