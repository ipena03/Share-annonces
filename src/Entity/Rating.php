<?php

// src/Entity/Rating.php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Annonce;
use App\Entity\User;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $announcement = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getAnnouncement(): ?Annonce
    {
        return $this->announcement;
    }

    public function setAnnouncement(?Annonce $announcement): static
    {
        $this->announcement = $announcement;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
