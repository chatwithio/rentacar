<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: '`messages`')]
class Message implements Timestampable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $message_content;

    #[ORM\Column(type: 'string', length: 25)]
    private string $message_type;

    #[ORM\Column(type: 'string')]
    private string $message_to;

    #[ORM\Column(type: 'string')]
    private string $message_from;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $license_number;

    #[ORM\Column(type: 'boolean')]
    private ?bool $sent;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $delivered;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $read;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    protected \DateTime $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    protected \DateTime $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageContent(): ?string
    {
        return $this->message_content;
    }

    public function setMessageContent(string $message_content): self
    {
        $this->message_content = $message_content;

        return $this;
    }

    public function getMessageType(): ?string
    {
        return $this->message_type;
    }

    public function setMessageType(string $message_type): self
    {
        $this->message_type = $message_type;

        return $this;
    }

    public function getMessageTo(): ?string
    {
        return $this->message_to;
    }

    public function setMessageTo(string $message_to): self
    {
        $this->message_to = $message_to;

        return $this;
    }

    public function getMessageFrom(): ?string
    {
        return $this->message_from;
    }

    public function setMessageFrom(string $message_from): self
    {
        $this->message_from = $message_from;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->license_number;
    }

    public function setLicenseNumber(?string $license_number): self
    {
        $this->license_number = $license_number;

        return $this;
    }

    public function isSent(): ?bool
    {
        return $this->sent;
    }

    public function setSent(bool $sent): self
    {
        $this->sent = $sent;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->delivered;
    }

    public function setDelivered(?bool $delivered): self
    {
        $this->delivered = $delivered;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->read;
    }

    public function setRead(?bool $read): self
    {
        $this->read = $read;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}