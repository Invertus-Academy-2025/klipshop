<?php

namespace App\Entity;

use App\Repository\BestSellingProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BestSellingProductRepository::class)]
class BestSellingProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private ?int $productId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private ?int $totalSold = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $syncedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    //Product id from prestashop
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTotalSold(): ?int
    {
        return $this->totalSold;
    }

    public function setTotalSold(int $totalSold): static
    {
        $this->totalSold = $totalSold;

        return $this;
    }

    public function getSyncedAt(): ?\DateTimeInterface
    {
        return $this->syncedAt;
    }

    public function setSyncedAt(\DateTimeInterface $syncedAt): static
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }
}
