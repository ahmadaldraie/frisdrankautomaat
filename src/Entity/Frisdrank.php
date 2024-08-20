<?php

namespace App\Entity;

use App\Repository\FrisdrankRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bundle\MakerBundle\Str;

#[ORM\Entity(repositoryClass: FrisdrankRepository::class)]
#[Table(name: 'frisdrank_frisdrankautomaat')]
class Frisdrank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: [
        'unsigned' => true
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $type = null;

    #[ORM\Column]
    private ?float $prijs = null;

    #[ORM\Column(options: [
        'unsigned' => true
    ])]
    private ?int $aantal = null;


    public function __construct (int $id, string $type, float $prijs, int $aantal) {
        $this->id = $id;
        $this->type = $type;
        $this->prijs = $prijs;
        $this->aantal = $aantal;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPrijs(): ?float
    {
        return $this->prijs;
    }

    public function setPrijs(float $prijs): static
    {
        $this->prijs = $prijs;

        return $this;
    }

    public function getAantal(): ?int
    {
        return $this->aantal;
    }

    public function setAantal(int $aantal): static
    {
        $this->aantal = $aantal;

        return $this;
    }
}
