<?php
// src/Entity/Product.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()] #[ORM\Table(name: "l3_product")]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", options: ["autoincrement" => true])]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    private string $name;

    #[ORM\Column(type:"float")]
    private float $price;

    #[ORM\Column(type:"integer")]
    private int $stock;

    // Getters and setters
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): float {
        return $this->price;
    }
    public function setPrice(float $price): self {
        $this->price = $price;
        return $this;
    }

    public function getStock(): int {
        return $this->stock;
    }
    public function setStock(int $stock): self {
        $this->stock = $stock;
        return $this;
    }
}
