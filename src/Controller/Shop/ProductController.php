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
    public function listAction(EntityManagerInterface $em): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        // récupérer tous les produits
        $products = $em->getRepository(Product::class)->findAll();

        // récupérer l'unique panier (si tu as bien une seule ligne actuellement)
        $cart = $em->getRepository(Cart::class)->findOneBy([]);

        $quantites = [];

        // vérifier si un panier existe
        if ($cart) {
            // récupérer les CartItems associés à ce panier uniquement
            $cartItems = $em->getRepository(CartItem::class)->findBy(['cart' => $cart]);

            // créer un tableau [productId => quantity]
            foreach ($cartItems as $item) {
                $productId = $item->getProduct()->getId();
                $quantites[$productId] = $item->getQuantity();
            }
        }

        // injecter la quantité dans chaque produit
        foreach ($products as $product) {
            $id = $product->getId();
            $product->in_cart = $quantites[$id] ?? 0;
        }

        // envoyer à la vue
        return $this->render("shop/product/list.html.twig", [
            'produits' => $products,
        ]);
    }

    #[Route('/panier', name: '_panier')]
    public function panierAction(EntityManagerInterface $em): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        // Récupère le panier (ici, le premier trouvé, à adapter selon la logique utilisateur)
        $cart = $em->getRepository(Cart::class)->findOneBy([]);
        if (!$cart) {
            // Panier vide : on peut renvoyer un tableau vide
            $items = [];
        } else {
            $items = $cart->getItems();

            // Supprimer les items avec une quantité de 0
            foreach ($items as $item) {
                if ($item->getQuantity() <= 0) {
                    $em->remove($item);
                }
            }
            $em->flush();

            // Recharger les items sans ceux qui ont été supprimés
            $items = $cart->getItems();
        }

        return $this->render('shop/product/panier.html.twig', ['items' => $items]);
    }


    #[Route('/add', name: '_add', methods: ['GET', 'POST'])]
    public function addProductAction(EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->add('submit', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();
            $this->addFlash('info', 'ajout réussie');
            return $this->redirectToRoute('shop_product');
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
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        // Récupération product_id, qty, ...
        $productId = $request->request->get('product_id');
        $qty = $request->request->getInt('quantity', 1);

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('shop_product');
        }

        $newStock = $product->getStock() - $qty;
        if ($newStock < 0) {
            // Il n’y a pas assez de stock => soit erreur, soit on met 0
            $this->addFlash('error', 'Stock insuffisant pour ce produit.');
            return $this->redirectToRoute('shop_product');
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
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

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
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

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
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy([]);
        if (!$cart) {
            $this->addFlash('info', 'Aucun panier à acheter.');
            return $this->redirectToRoute('shop_product_panier');
        }

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $qty = $item->getQuantity();

            // On supprime le CartItem
            $em->remove($item);

            // Inutile de faire $em->persist($product) car $product est déjà “managé”
            // par l'EntityManager, la modification du stock sera flushée.
        }

        $em->flush();

        $this->addFlash('success', 'Achat effectué : le panier est vidé et le stock a été mis à jour.');
        return $this->redirectToRoute('shop_product_panier');
    }

    #[Route('/modify', name: '_modify')]
    public function modifyAction(Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        $firstProduct = $em->getRepository(Product::class)->findOneBy([], ['id' => 'ASC']);

        if (!$firstProduct) {
            // Aucun produit n'existe encore, il faut gérer ce cas (afficher un message d'erreur, etc.)
            throw new \Exception('Aucun produit dans la base');
        }
        // On ne part pas d'une entité, mais d'un "DTO" vide
        // (ou d'un stdClass si tu préfères)
        $data = new \stdClass();
        $data->product = $firstProduct;
        $data->name    = $firstProduct->getName();
        $data->stock   = $firstProduct->getStock();
        $data->price   = $firstProduct->getPrice();

        // On crée le formulaire
        $form = $this->createForm(ProductStockType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $data->product;

            if ($form->get('update')->isClicked()) {
                $product->setName($data->name);
                $product->setStock($data->stock);
                $product->setPrice($data->price);
                $em->flush();

                $this->addFlash('success', 'Le produit a été mis à jour.');
            } elseif ($form->get('delete')->isClicked()) {
                $em->remove($product);
                $em->flush();

                $this->addFlash('success', 'Le produit a été supprimé.');
            }

            return $this->redirectToRoute('shop_product');
        }

        return $this->render('shop/product/modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}

