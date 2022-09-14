<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 10)]
    private $matricula;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'string', length: 15)]
    private $wa_id;

    #[ORM\OneToMany(mappedBy: 'matricula', targetEntity: CarPhotos::class)]
    private $carPhotos;

    #[ORM\Column(type: 'string', length: 15)]
    private $telFrom;


    public function __construct()
    {
        $this->carPhotos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricula(): ?string
    {
        return $this->matricula;
    }

    public function setMatricula(string $matricula): self
    {
        $this->matricula = $matricula;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getWaId(): ?string
    {
        return $this->wa_id;
    }

    public function setWaId(string $wa_id): self
    {
        $this->wa_id = $wa_id;

        return $this;
    }

    /**
     * @return Collection<int, CarPhotos>
     */
    public function getCarPhotos(): Collection
    {
        return $this->carPhotos;
    }

    public function addCarPhoto(CarPhotos $carPhoto): self
    {
        if (!$this->carPhotos->contains($carPhoto)) {
            $this->carPhotos[] = $carPhoto;
            $carPhoto->setMatricula($this);
        }

        return $this;
    }

    public function removeCarPhoto(CarPhotos $carPhoto): self
    {
        if ($this->carPhotos->removeElement($carPhoto)) {
            // set the owning side to null (unless already changed)
            if ($carPhoto->getMatricula() === $this) {
                $carPhoto->setMatricula(null);
            }
        }

        return $this;
    }

    public function getTelFrom(): ?string
    {
        return $this->telFrom;
    }

    public function setTelFrom(string $telFrom): self
    {
        $this->telFrom = $telFrom;

        return $this;
    }

}
