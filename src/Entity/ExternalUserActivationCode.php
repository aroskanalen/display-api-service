<?php

namespace App\Entity;

use App\Repository\ExternalUserActivationCodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExternalUserActivationCodeRepository::class)
 */
class ExternalUserActivationCode extends AbstractBaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Tenant::class, inversedBy="externalUserActivationCodes")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Tenant $tenant;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $code;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private ?\DateTime $codeExpire;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $username;

    /**
     * @ORM\Column(type="json")
     */
    private ?array $roles = [];

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getCodeExpire(): ?\DateTime
    {
        return $this->codeExpire;
    }

    public function setCodeExpire(?\DateTime $codeExpire): void
    {
        $this->codeExpire = $codeExpire;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): void
    {
        $this->roles = $roles;
    }
}
