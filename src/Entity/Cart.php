<?php
// src/Entity/Cart.php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()] #[ORM\Table(name: "l3_cart")]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy:"cart", targetEntity: CartItem::class, cascade:["persist", "remove"])]
    private Collection $items;

    #[ORM\OneToOne(mappedBy: 'cart', targetEntity: User::class)]
    private ?User $owner = null;


    public function __construct() {
        $this->items = new ArrayCollection();
    }

    // Getters and setters
    public function getId(): ?int {
        return $this->id;
    }
    public function getItems(): Collection {
        return $this->items;
    }
    public function addItem(CartItem $item): self {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCart($this);
        }
        return $this;
    }
    public function removeItem(CartItem $item): self {
        if ($this->items->removeElement($item)) {
            if ($item->getCart() === $this) {
                $item->setCart(null);
            }
        }
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }
}
