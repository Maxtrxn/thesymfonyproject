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
            // Sélecteur pour choisir un produit
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'Produit sélectionné',
            ])

            // Champ pour modifier le nom du produit
            ->add('name', null, [
                'label' => 'Nom du produit',
            ])
            // Champ pour saisir le nouveau prix
            ->add('price', null, [
                'label' => 'Prix',
            ])
            // Champ pour saisir la nouvelle quantité
            ->add('stock', IntegerType::class, [
                'label' => 'Quantité en stock',
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
