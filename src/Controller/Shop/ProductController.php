<?php

namespace App\Controller\Shop;

use App\Entity\CartItem;
use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Config\Doctrine\Orm\EntityManagerConfig;

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

        return $this->render("shop/product/list.html.twig", ['produits' => $produits]);
    }

    #[Route('/panier', name: '_panier')]
    public function panierAction(EntityManagerInterface $em): Response
    {

        $products = $em->getRepository(Product::class)->findAll();


        $items = [
            ['libelle' => 'Produit 1', 'prix' => 10, 'quantite' => 10],
            ['libelle' => 'Produit 2', 'prix' => 20, 'quantite' => 20],
        ];

        return $this->render("shop/product/panier.html.twig", ['panier' => $items]);
    }

    #[Route('/add/product', name: '_add_product', methods: ['GET', 'POST'])]
    public function addProductAction(EntityManagerInterface $em): Response
    {

        return $this->render('shop/product/add_produit.html.twig');
    }

}

