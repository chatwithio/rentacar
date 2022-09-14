<?php

namespace App\Entity;

use App\Repository\CarPhotosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarPhotosRepository::class)]
class CarPhotos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $photo_id;

    #[ORM\Column(type: 'datetime')]
    private $created;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhotoId(): ?string
    {
        return $this->photo_id;
    }

    public function setPhotoId(string $photo_id): self
    {
        $this->photo_id = $photo_id;

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
    
}
