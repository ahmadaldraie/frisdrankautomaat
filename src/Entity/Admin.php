<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[Table(name: 'admin_frisdrankautomaat')]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $wachtwoord = null;

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

    public function getWachtwoord(): ?string
    {
        return $this->wachtwoord;
    }

    public function setWachtwoord(string $wachtwoord): static
    {
        $this->wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);

        return $this;
    }
}
