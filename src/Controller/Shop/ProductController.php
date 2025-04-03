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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/shop/product', name: 'shop_product')]
final class ProductController extends AbstractController
{
    #[Route('', name: '')]
    public function listAction(EntityManagerInterface $em): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit aux super-administrateurs.');
        }

        $products = $em->getRepository(Product::class)->findAll();
        $cart = $this->getUser()?->getCart();
        $quantites = [];

        if ($cart) {
            foreach ($cart->getItems() as $item) {
                $quantites[$item->getProduct()->getId()] = $item->getQuantity();
            }
        }

        foreach ($products as $product) {
            $product->in_cart = $quantites[$product->getId()] ?? 0;
        }

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

        $cart = $this->getUser()?->getCart();
        $items = $cart ? $cart->getItems() : [];

        foreach ($items as $item) {
            if ($item->getQuantity() <= 0) {
                $em->remove($item);
            }
        }
        $em->flush();

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

        return $this->render('shop/product/add_produit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/add_to_cart', name: '_add_to_cart', methods: ['POST'])]
    public function addToCartAction(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User || in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        $productId = $request->request->get('product_id');
        $qty = $request->request->getInt('quantity', 1);

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Produit introuvable');
            return $this->redirectToRoute('shop_product');
        }

        if ($product->getStock() < $qty) {
            $this->addFlash('error', 'Stock insuffisant pour ce produit.');
            return $this->redirectToRoute('shop_product');
        }

        $product->setStock($product->getStock() - $qty);
        $cart = $user->getCart();

        $existingItem = $em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        if (!$existingItem) {
            $item = new CartItem();
            $item->setCart($cart);
            $item->setProduct($product);
            $item->setQuantity($qty);
            $em->persist($item);
        } else {
            $existingItem->setQuantity($existingItem->getQuantity() + $qty);
        }

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('shop_product_panier');
    }

    #[Route('/remove_item', name: '_remove_item', methods: ['POST'])]
    public function removeItemAction(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || $this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit.');
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

        $product = $item->getProduct();
        $product->setStock($product->getStock() + $item->getQuantity());

        $em->remove($item);
        $em->flush();

        $this->addFlash('success', 'Item supprimé du panier.');
        return $this->redirectToRoute('shop_product_panier');
    }

    #[Route('/clear_cart', name: '_clear_cart', methods: ['POST'])]
    public function clearCartAction(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user || $this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        $cart = $user->getCart();
        if (!$cart) {
            $this->addFlash('info', 'Pas de panier à vider.');
            return $this->redirectToRoute('shop_product_panier');
        }

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
        $user = $this->getUser();
        if (!$user || $this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        $cart = $user->getCart();
        if (!$cart) {
            $this->addFlash('info', 'Aucun panier à acheter.');
            return $this->redirectToRoute('shop_product_panier');
        }

        foreach ($cart->getItems() as $item) {
            $em->remove($item);
        }

        $em->flush();

        $this->addFlash('success', 'Achat effectué : le panier est vidé.');
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
            throw new \Exception('Aucun produit dans la base');
        }

        $data = new \stdClass();
        $data->product = $firstProduct;
        $data->name    = $firstProduct->getName();
        $data->stock   = $firstProduct->getStock();
        $data->price   = $firstProduct->getPrice();

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

    #[Route('/get-product/{id}', name: '_get_product_data', methods: ['GET'])]
    public function getProductData(Product $product): JsonResponse
    {
        return new JsonResponse([
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
        ]);
    }
}
