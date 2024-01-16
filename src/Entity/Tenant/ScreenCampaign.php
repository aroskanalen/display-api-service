<?php

declare(strict_types=1);

namespace App\Entity\Tenant;

use App\Entity\Traits\RelationsModifiedAtTrait;
use App\Repository\ScreenCampaignRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreenCampaignRepository::class)]
#[ORM\Index(fields: ['relationsModifiedAt'], name: 'relations_modified_at_idx')]
#[ORM\Index(fields: ['modifiedAt'], name: 'modified_at_idx')]
class ScreenCampaign extends AbstractTenantScopedEntity
{
    use RelationsModifiedAtTrait;

    #[ORM\ManyToOne(targetEntity: Playlist::class, inversedBy: 'screenCampaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private Playlist $campaign;

    #[ORM\ManyToOne(targetEntity: Screen::class, inversedBy: 'screenCampaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private Screen $screen;

    public function getCampaign(): Playlist
    {
        return $this->campaign;
    }

    public function setCampaign(Playlist $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getScreen(): Screen
    {
        return $this->screen;
    }

    public function setScreen(Screen $screen): self
    {
        $this->screen = $screen;

        return $this;
    }
}
