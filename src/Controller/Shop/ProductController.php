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


    #[Route('/list', name: 'list')]
    public function listProducts(): Response
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
        return $this->();
    }

}

