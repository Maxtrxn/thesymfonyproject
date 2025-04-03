<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "l3_user")]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $surname = null;

    #[ORM\Column(length: 2)]
    private ?string $country = null;

    /**
     * Relation OneToOne vers Cart.
     * Ce champ est le "côté inverse", mappé par le champ "owner" dans Cart.
     */
    #[ORM\OneToOne(targetEntity: Cart::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "cart_id", referencedColumnName: "id", nullable: false)]
    private ?Cart $cart = null;


    /*
     * -----------------------
     * GETTERS / SETTERS
     * -----------------------
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Clear any temporary sensitive data.
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // $this->plainPassword = null;
    }

    /**
     * OneToOne -> le champ cart
     */
    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;
        // Mise à jour du côté propriétaire dans l'entité Cart
        if ($cart->getOwner() !== $this) {
            $cart->setOwner($this);
        }
        return $this;
    }

    public function __construct()
    {
        // Créer un panier vide et lier cet utilisateur comme propriétaire
        $cart = new Cart();
        $this->setCart($cart);
    }

}