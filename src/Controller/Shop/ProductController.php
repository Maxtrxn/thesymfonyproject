<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shop/product', name: 'shop_product')]
final class ProductController extends AbstractController
{
    #[Route('', name: '')]
    public function indexAction(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/Shop/ProductController.php',
        ]);
    }


    #[Route('/list', name: '_list')]
    public function listAction(): Response
    {
        /*
        $entityManager = $this->getDoctrine()->getManager();
        $products = $entityManager->getRepository(Product::class)->findAll();

        $productData = [];
        foreach ($products as $product) {
            $productData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ];
        }
        */
        $produits = [
            ['libelle' => 'Produit 1', 'prix' => 10, 'stock' => 10],
            ['libelle' => 'Produit 2', 'prix' => 20, 'stock' => 20],
        ];

        return $this->render("shop/product/list.html.twig",  ['produits' => $produits]);
    }

    #[Route('/panier', name: '_panier')]
    public function panierAction(): Response
    {
        /*
        $entityManager = $this->getDoctrine()->getManager();
        $products = $entityManager->getRepository(Product::class)->findAll();

        $productData = [];
        foreach ($products as $product) {
            $productData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ];
        }
        */
        $items = [
            ['libelle' => 'Produit 1', 'prix' => 10, 'quantite' => 10],
            ['libelle' => 'Produit 2', 'prix' => 20, 'quantite' => 20],
        ];

        return $this->render("shop/product/panier.html.twig",  ['panier' => $items]);
    }

}

