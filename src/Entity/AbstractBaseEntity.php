<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Interfaces\BlameableInterface;
use App\Entity\Interfaces\TimestampableInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractBaseEntity implements BlameableInterface, TimestampableInterface
{
    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $modifiedAt;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: false, options: ['default' => ''])]
    private string $createdBy = '';

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: false, options: ['default' => ''])]
    private string $modifiedBy = '';

    public function __construct()
    {
        $this->modifiedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Get the Ulid.
     */
    public function getId(): ?Ulid
    {
        return $this->id;
    }

    /**
     * Set the Ulid.
     */
    public function setId(Ulid $id): self
    {
        $this->id = $id;

        $this->createdAt = $this->id->getDateTime();

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    #[Ignore]
    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = isset($this->id) ? $this->id->getDateTime() : new \DateTimeImmutable();

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return isset($this->modifiedAt) ? $this->modifiedAt : null;
    }

    #[Ignore]
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setModifiedAt(): self
    {
        $this->modifiedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getModifiedBy(): string
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(string $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
