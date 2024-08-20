<?php

namespace App\Entity;

use App\Repository\MuntjeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

#[ORM\Entity(repositoryClass: MuntjeRepository::class)]
#[Table(name: 'muntje_frisdrankautomaat')]
class Muntje
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: [
        'unsigned' => true
    ])]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $waarde = null;

    #[ORM\Column(options: [
        'unsigned' => true
    ])]
    private ?int $aantal = null;

    public function __construct(int $id, float $waarde, int $aantal) {
        $this->id = $id;
        $this->waarde = $waarde;
        $this->aantal = $aantal;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWaarde(): ?float
    {
        return $this->waarde;
    }

    public function setWaarde(float $waarde): static
    {
        $this->waarde = $waarde;

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
