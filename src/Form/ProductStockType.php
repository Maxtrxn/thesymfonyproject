<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Sélecteur pour choisir un produit
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',  //ce qui s'affiche dans la liste
            ])
            // Champ pour saisir la nouvelle quantité
            ->add('stock', IntegerType::class, [
                'label' => 'Quantité en stock',
            ])
        ;
    }
}
