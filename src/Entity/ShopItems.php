<?php

namespace App\Entity;

use App\Repository\ShopItemsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopItemsRepository::class)]
class ShopItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $price = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $defcription = null;

    #[ORM\OneToMany(mappedBy: 'shopItem', targetEntity: ShopCart::class, orphanRemoval: true)]
    private Collection $shopCarts;

    public function __construct()
    {
        $this->shopCarts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDefcription(): ?string
    {
        return $this->defcription;
    }

    public function setDefcription(string $defcription): static
    {
        $this->defcription = $defcription;

        return $this;
    }

    /**
     * @return Collection<int, ShopCart>
     */
    public function getShopCarts(): Collection
    {
        return $this->shopCarts;
    }

    public function addShopCart(ShopCart $shopCart): static
    {
        if (!$this->shopCarts->contains($shopCart)) {
            $this->shopCarts->add($shopCart);
            $shopCart->setShopItem($this);
        }

        return $this;
    }

    public function removeShopCart(ShopCart $shopCart): static
    {
        if ($this->shopCarts->removeElement($shopCart)) {
            // set the owning side to null (unless already changed)
            if ($shopCart->getShopItem() === $this) {
                $shopCart->setShopItem(null);
            }
        }

        return $this;
    }
}
