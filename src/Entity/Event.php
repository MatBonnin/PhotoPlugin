<?php

namespace Sylius\Plugin\PhotoPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="event")
 */
class Event implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $startDate = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?\DateTimeInterface $endDate = null;

    /**
     * @ORM\ManyToMany(targetEntity=Photographer::class, inversedBy="events", cascade={"persist"})
     * @ORM\JoinTable(name="event_photographers",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="photographer_id", referencedColumnName="id")}
     * )
     */
    private Collection $photographers;

    public function __construct()
    {
        $this->photographers = new ArrayCollection();
    }

    // Getters et Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return Collection|Photographer[]
     */
    public function getPhotographers(): Collection
    {
        return $this->photographers;
    }

    public function addPhotographer(Photographer $photographer): self
    {
        if (!$this->photographers->contains($photographer)) {
            $this->photographers[] = $photographer;
            $photographer->addEvent($this);
        }

        return $this;
    }

    public function removePhotographer(Photographer $photographer): self
    {
        if ($this->photographers->removeElement($photographer)) {
            $photographer->removeEvent($this);
        }

        return $this;
    }
}
