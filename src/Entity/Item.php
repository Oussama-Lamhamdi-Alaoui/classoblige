<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
class Item
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemName;

    /**
     * @ORM\Column(type="float")
     */
    private $itemPrice;

    /**
     * @ORM\Column(type="integer")
     */
    private $itemStock;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemImage;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemSize;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $itemDateAdded;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $itemOnSale;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $itemSale;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    public function setItemName(string $itemName): self
    {
        $this->itemName = $itemName;

        return $this;
    }

    public function getItemPrice(): ?float
    {
        return $this->itemPrice;
    }

    public function setItemPrice(float $itemPrice): self
    {
        $this->itemPrice = $itemPrice;

        return $this;
    }

    public function getItemStock(): ?int
    {
        return $this->itemStock;
    }

    public function setItemStock(int $itemStock): self
    {
        $this->itemStock = $itemStock;

        return $this;
    }

    public function getItemImage(): ?string
    {
        return $this->itemImage;
    }

    public function setItemImage(string $itemImage): self
    {
        $this->itemImage = $itemImage;

        return $this;
    }

    public function getItemSize(): ?string
    {
        return $this->itemSize;
    }

    public function setItemSize(string $itemSize): self
    {
        $this->itemSize = $itemSize;

        return $this;
    }

    public function getItemDateAdded(): ?\DateTimeInterface
    {
        return $this->itemDateAdded;
    }

    public function setItemDateAdded(\DateTimeInterface $itemDateAdded): self
    {
        $this->itemDateAdded = $itemDateAdded;

        return $this;
    }

    public function getItemType(): ?string
    {
        return $this->itemType;
    }

    public function setItemType(string $itemType): self
    {
        $this->itemType = $itemType;

        return $this;
    }

    public function getItemOnSale(): ?bool
    {
        return $this->itemOnSale;
    }

    public function setItemOnSale(bool $itemOnSale): self
    {
        $this->itemOnSale = $itemOnSale;

        return $this;
    }

    public function getItemSale(): ?float
    {
        return $this->itemSale;
    }

    public function setItemSale(?float $itemSale): self
    {
        $this->itemSale = $itemSale;

        return $this;
    }
}
