<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'Produit sélectionné',
                'attr' => ['id' => 'product_selector'],
                'choice_attr' => function ($product) {
                    return [
                        'data-id' => $product->getId(),
                        'data-name' => $product->getName(),
                        'data-price' => $product->getPrice(),
                        'data-stock' => $product->getStock(),
                    ];
                },
            ])
            ->add('name', null, [
                'label' => 'Nom du produit',
                'attr' => ['id' => 'product_name']
            ])
            ->add('price', null, [
                'label' => 'Prix',
                'attr' => ['id' => 'product_price']
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Quantité en stock',
                'attr' => ['id' => 'product_stock']
            ])
            ->add('update', SubmitType::class, [
                'label' => 'Mettre à jour',
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Supprimer',
            ])
        ;
    }
}
