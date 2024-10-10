<?php

namespace Sylius\Plugin\PhotoPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Product\ProductVariant;

/**
 * @ORM\Entity
 * @ORM\Table(name="quantity_price")
 */
class QuantityPrice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ProductVariant::class, inversedBy="quantityPrices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productVariant;

    /**
     * @ORM\Column(type="integer")
     */
    private $minQuantity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxQuantity;

    /**
     * @ORM\Column(type="integer") // Prix en centimes
     */
    private $price;

    // Getters et setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariant $productVariant): self
    {
        $this->productVariant = $productVariant;
        return $this;
    }

    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(int $minQuantity): self
    {
        $this->minQuantity = $minQuantity;
        return $this;
    }

    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity(?int $maxQuantity): self
    {
        $this->maxQuantity = $maxQuantity;
        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }
}
