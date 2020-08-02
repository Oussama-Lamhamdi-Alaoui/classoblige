<?php

namespace App\Entity;

use App\Repository\ExpensesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpensesRepository::class)
 */
class Expenses
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $insurance;

    /**
     * @ORM\Column(type="float")
     */
    private $utility;

    /**
     * @ORM\Column(type="float")
     */
    private $maintenance;

    /**
     * @ORM\Column(type="date")
     */
    private $month;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInsurance(): ?float
    {
        return $this->insurance;
    }

    public function setInsurance(float $insurance): self
    {
        $this->insurance = $insurance;

        return $this;
    }

    public function getUtility(): ?float
    {
        return $this->utility;
    }

    public function setUtility(float $utility): self
    {
        $this->utility = $utility;

        return $this;
    }

    public function getMaintenance(): ?float
    {
        return $this->maintenance;
    }

    public function setMaintenance(float $maintenance): self
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getMonth(): ?\DateTimeInterface
    {
        return $this->month;
    }

    public function setMonth(\DateTimeInterface $month): self
    {
        $this->month = $month;

        return $this;
    }
}
