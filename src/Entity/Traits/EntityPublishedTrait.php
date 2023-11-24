<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * @internal
 */
trait EntityPublishedTrait
{
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $publishedFrom;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $publishedTo;

    public function getPublishedFrom(): ?\DateTime
    {
        return $this->publishedFrom;
    }

    public function setPublishedFrom(?\DateTime $publishedFrom): self
    {
        $this->publishedFrom = $publishedFrom;

        return $this;
    }

    public function getPublishedTo(): ?\DateTime
    {
        return $this->publishedTo;
    }

    public function setPublishedTo(?\DateTime $publishedTo): self
    {
        $this->publishedTo = $publishedTo;

        return $this;
    }
}
