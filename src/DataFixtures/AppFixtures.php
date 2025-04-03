<?php

namespace App\DataFixtures;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1) Récupère tous les utilisateurs
        $users = $manager->getRepository(User::class)->findAll();

        // 2) Pour chacun, crée un cart vide
        foreach ($users as $user) {
            $cart = new Cart();
            $cart->setOwner($user);
            // pas besoin d’ajouter d’items => empty cart
            $manager->persist($cart);
        }

        // 3) Flush
        $manager->flush();
    }
}
