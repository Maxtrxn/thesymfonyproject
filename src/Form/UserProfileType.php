<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('surname', TextType::class, [
                'label' => 'Nom de famille',
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'placeholder' => 'Choisissez votre pays',
            ])
            // Champ de mot de passe : non mappé pour éviter de modifier le mot de passe si l'utilisateur le laisse vide.
            ->add('password', PasswordType::class, [
                'label'    => 'Mot de passe (laisser vide pour ne pas changer)',
                'mapped'   => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
