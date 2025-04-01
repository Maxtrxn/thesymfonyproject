<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rolesChoices = [
            'Utilisateur' => 'ROLE_USER',
            'Admin' => 'ROLE_ADMIN',
        ];

        if ($options['show_superadmin']) {
            $rolesChoices['Super Admin'] = 'ROLE_SUPER_ADMIN';
        }

        $builder
            ->add('name', TextType::class, [ 'label' => 'Nom'])
            ->add('surname', TextType::class, [ 'label' => 'Prénom'])
            ->add('roles', ChoiceType::class, [
                'choices'  => $rolesChoices,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Rôle(s)',
                'mapped' => true,
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'placeholder' => 'Choisissez votre pays',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'show_superadmin' => false,
        ]);
    }
}
