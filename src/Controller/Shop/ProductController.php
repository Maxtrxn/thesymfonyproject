<?php

namespace App\Controller\Shop;

use App\Entity\CartItem;
use App\Entity\Cart;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function listAction(EntityManagerInterface $em): Response
    {

        $products = $em->getRepository(Product::class)->findAll();

        return $this->render("shop/product/list.html.twig", ['produits' => $products]);
    }

    #[Route('/panier', name: '_panier')]
    public function panierAction(EntityManagerInterface $em): Response
    {

        $products = $em->getRepository(Cart::class)->findAll();


        $items = [
            ['libelle' => 'Produit 1', 'prix' => 10, 'quantite' => 10],
            ['libelle' => 'Produit 2', 'prix' => 20, 'quantite' => 20],
        ];

        return $this->render("shop/product/panier.html.twig", ['panier' => $items]);
    }

    #[Route('/add/product', name: '_add_product', methods: ['GET', 'POST'])]
    public function addProductAction(EntityManagerInterface $em, Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            $this->addFlash('info', 'ajout réussie');
            return $this->redirectToRoute('shop_product_list');
        }

        if ($form->isSubmitted()){
            $this->addFlash('info', 'formulaire incorrect');
        }

        $args = array(
            'form' => $form,
        );

        // Afficher le formulaire
        return $this->render('shop/product/add_produit.html.twig', $args);
    }

}

