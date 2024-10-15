<?php

namespace Sylius\Plugin\PhotoPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUser;
use Symfony\Component\Security\Core\User\UserInterface;

use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="photographer")
 */
class Photographer implements ResourceInterface, UserInterface
{


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Sylius\Component\Core\Model\AdminUser", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="admin_user_id", referencedColumnName="id", nullable=false)
     */
    private $adminUser;

    // ... (vos autres méthodes)



    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\ManyToMany(targetEntity="Event", mappedBy="photographers", cascade={"persist"})
     */
    private $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getAdminUser(): AdminUser
    {
        return $this->adminUser;
    }

    public function setAdminUser(AdminUser $adminUser): self
    {
        $this->adminUser = $adminUser;

        return $this;
    }

    // Implémentation de UserInterface

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->email;
    }




    public function getSalt()
    {
        // Non nécessaire avec bcrypt ou argon2
        return null;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        // Chaque utilisateur a au moins le rôle ROLE_PHOTOGRAPHER
        $roles = $this->roles;
        $roles[] = 'ROLE_PHOTOGRAPHER';

        return array_unique($roles);
    }

    public function eraseCredentials()
    {
        // Si des informations sensibles sont temporairement stockées, effacez-les ici
    }

    // Implémentation de PasswordAuthenticatedUserInterface



    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Getters et setters pour les autres champs

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->addPhotographer($this); // Synchronisation côté Event
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            $event->removePhotographer($this);
        }

        return $this;
    }
}
