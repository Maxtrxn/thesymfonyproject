<?php

namespace App\Controller;

use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/edit', name: 'profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            // Si aucun utilisateur n'est connecté, redirige vers la page de connexion
            return $this->redirectToRoute('/auth');
        }

        // Crée le formulaire pour éditer le profil avec les données de l'utilisateur courant
        $form = $this->createForm(UserProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le mot de passe saisi (non mappé)
            $plainPassword = $form->get('password')->getData();
            // Si le champ est rempli, on met à jour le mot de passe de l'utilisateur
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            // Sauvegarde des modifications en base
            $entityManager->flush();

            // Ajoute un message flash pour informer l'utilisateur
            $this->addFlash('success', 'Votre profil a été modifié avec succès.');

            // Redirige vers la page d'accueil (ou vers la liste des produits si ce n'est pas un super-admin)
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('accueil');
            } else {
                return $this->redirectToRoute('shop_product');
            }

        }

        // Affiche le formulaire pré-rempli
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
