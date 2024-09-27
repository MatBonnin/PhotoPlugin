<?php

namespace Sylius\Plugin\PhotoPlugin\temp;

use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
class ProductVariant extends BaseProductVariant
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $size = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $format = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?float $priceModifier = null;

    // Getters et setters

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): void
    {
        $this->size = $size;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    public function getPriceModifier(): ?float
    {
        return $this->priceModifier;
    }

    public function setPriceModifier(?float $priceModifier): void
    {
        $this->priceModifier = $priceModifier;
    }
}
