<?php

namespace Sylius\Plugin\PhotoPlugin\temp;

use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Plugin\PhotoPlugin\Entity\Event;
use Sylius\Plugin\PhotoPlugin\Entity\Photographer;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct
{
    /**
     * @ORM\ManyToOne(targetEntity=Photographer::class)
     * @ORM\JoinColumn(name="photographer_id", referencedColumnName="id", nullable=true)
     */
    private ?Photographer $photographer = null;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class)
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     */
    private ?Event $event = null;

    // Getters et setters

    public function getPhotographer(): ?Photographer
    {
        return $this->photographer;
    }

    public function setPhotographer(?Photographer $photographer): void
    {
        $this->photographer = $photographer;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    public function addTaxon(Taxon $taxon): void
    {
        if (!$this->getTaxons()->contains($taxon)) {
            $this->getTaxons()->add($taxon);
        }
    }
}
