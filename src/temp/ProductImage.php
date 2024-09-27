<?php

namespace Sylius\Plugin\PhotoPlugin\temp;

use Sylius\Component\Core\Model\ProductImage as BaseProductImage;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_image")
 */
class ProductImage extends BaseProductImage
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $isFeatured = false;

    // Getters et setters

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): void
    {
        $this->isFeatured = $isFeatured;
    }
}
