<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
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
    private string $messageContent;

    #[ORM\Column(type: 'string', length: 25)]
    private string $messageType;

    #[ORM\Column(type: 'string')]
    private string $messageTo;

    #[ORM\Column(type: 'string')]
    private string $messageFrom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $licenseNumber;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $sent;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $delivered;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $read;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected \DateTime $createdAt;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected \DateTime $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageContent(): ?string
    {
        return $this->messageContent;
    }

    public function setMessageContent(string $messageContent): self
    {
        $this->messageContent = $messageContent;

        return $this;
    }

    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function getMessageTo(): ?string
    {
        return $this->messageTo;
    }

    public function setMessageTo(string $messageTo): self
    {
        $this->messageTo = $messageTo;

        return $this;
    }

    public function getMessageFrom(): ?string
    {
        return $this->messageFrom;
    }

    public function setMessageFrom(string $messageFrom): self
    {
        $this->messageFrom = $messageFrom;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;

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