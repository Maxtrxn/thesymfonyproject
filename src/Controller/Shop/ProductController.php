<?php

namespace App\Controller\Shop;

use App\Entity\CartItem;
use App\Entity\Cart;
use App\Entity\Product;
use App\Form\ProductType;
use App\Form\ProductStockType;

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
        // Récupère le panier (ici, le premier trouvé, à adapter selon la logique utilisateur)
        $cart = $em->getRepository(Cart::class)->findOneBy([]);
        if (!$cart) {
            // Panier vide : on peut renvoyer un tableau vide
            $items = [];
        } else {
            $items = $cart->getItems();
        }

        return $this->render('shop/product/panier.html.twig', ['items' => $items]);
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

    #[Route('/add_to_cart', name: '_add_to_cart', methods: ['POST'])]
    public function addToCartAction(Request $request, EntityManagerInterface $em): Response
    {
        // Récupération product_id, qty, ...
        $productId = $request->request->get('product_id');
        $qty = $request->request->getInt('quantity', 1);

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('shop_product_list');
        }

        $newStock = $product->getStock() - $qty;
        if ($newStock < 0) {
            // Il n’y a pas assez de stock => soit erreur, soit on met 0
            $this->addFlash('error', 'Stock insuffisant pour ce produit.');
            return $this->redirectToRoute('shop_product_list');
        }
        $product->setStock($newStock);

        // Récupération ou création du Cart...
        $cart = $em->getRepository(Cart::class)->findOneBy([]);
        if (!$cart) {
            $cart = new Cart();
            $em->persist($cart);
            $em->flush();
        }

        // On cherche un CartItem déjà existant avec ce cart + product
        $existingItem = $em->getRepository(CartItem::class)
            ->findOneBy([
                'cart' => $cart,
                'product' => $product
            ]);

        if (!$existingItem) {
            // On crée un nouvel item
            $item = new CartItem();
            $item->setCart($cart);
            $item->setProduct($product);
            $item->setQuantity($qty);

            $em->persist($item);
        } else {
            // On ajoute la quantité
            $existingItem->setQuantity(
                $existingItem->getQuantity() + $qty
            );
        }

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('shop_product_panier');
    }


    #[Route('/remove_item', name: '_remove_item', methods: ['POST'])]
    public function removeItemAction(Request $request, EntityManagerInterface $em): Response
    {
        $itemId = $request->request->get('item_id');
        if (!$itemId) {
            $this->addFlash('error', 'Aucun identifiant de ligne à supprimer');
            return $this->redirectToRoute('shop_product_panier');
        }

        $item = $em->getRepository(CartItem::class)->find($itemId);
        if (!$item) {
            $this->addFlash('error', 'Item introuvable');
            return $this->redirectToRoute('shop_product_panier');
        }

        // On rétablit le stock
        $product = $item->getProduct();
        $qty = $item->getQuantity();
        $product->setStock($product->getStock() + $qty);

        // Suppression
        $em->remove($item);
        $em->flush();

        $this->addFlash('success', 'Item supprimé du panier.');
        return $this->redirectToRoute('shop_product_panier');
    }

    #[Route('/clear_cart', name: '_clear_cart', methods: ['POST'])]
    public function clearCartAction(EntityManagerInterface $em): Response
    {
        // Récupérer le Cart (on suppose qu’on n’a qu’un seul panier, ou on récupère le panier de l’utilisateur)
        $cart = $em->getRepository(Cart::class)->findOneBy([]);
        if (!$cart) {
            $this->addFlash('info', 'Pas de panier à vider.');
            return $this->redirectToRoute('shop_product_panier');
        }

        // Boucler sur les CartItem et remove un par un
        foreach ($cart->getItems() as $item) {
            $em->remove($item);
        }

        // Pour chaque item, on rétablit le stock
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $product->setStock($product->getStock() + $item->getQuantity());

            $em->remove($item);
        }

        $em->flush();

        $this->addFlash('info', 'Le panier est maintenant vide.');
        return $this->redirectToRoute('shop_product_panier');
    }

    #[Route('/purchase_cart', name: '_purchase_cart', methods: ['POST'])]
    public function purchaseCartAction(EntityManagerInterface $em): Response
    {
        $cart = $em->getRepository(Cart::class)->findOneBy([]);
        if (!$cart) {
            $this->addFlash('info', 'Aucun panier à acheter.');
            return $this->redirectToRoute('shop_product_panier');
        }

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $qty = $item->getQuantity();

            // On diminue le stock du produit
            $newStock = $product->getStock() - $qty;
            $product->setStock($newStock < 0 ? 0 : $newStock);

            // On supprime le CartItem
            $em->remove($item);

            // Inutile de faire $em->persist($product) car $product est déjà “managé”
            // par l'EntityManager, la modification du stock sera flushée.
        }

        $em->flush();

        $this->addFlash('success', 'Achat effectué : le panier est vidé et le stock a été mis à jour.');
        return $this->redirectToRoute('shop_product_panier');
    }

    #[Route('/adjust_stock', name: '_adjust_stock')]
    public function adjustStockAction(Request $request, EntityManagerInterface $em): Response
    {
        // On ne part pas d'une entité, mais d'un "DTO" vide
        // (ou d'un stdClass si tu préfères)
        $data = new \stdClass();
        $data->product = null;
        $data->stock = 0;

        // On crée le formulaire
        $form = $this->createForm(ProductStockType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données
            $product = $data->product; // c'est un Product
            $newStock = $data->stock;  // c'est l'entier

            // Mise à jour
            $product->setStock($newStock);
            $em->flush();

            $this->addFlash('success', 'Le stock du produit a été mis à jour.');
            return $this->redirectToRoute('shop_product_list');
        }

        return $this->render('shop/product/adjust_stock.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}

