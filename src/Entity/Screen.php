<?php

namespace App\Entity;

use App\Repository\ScreenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ScreenRepository::class)
 */
class Screen
{
    use EntityIdTrait;
    use EntityTitleDescTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="integer")
     */
    private $resolutionWidth;

    /**
     * @ORM\Column(type="integer")
     */
    private $resolutionHeight;

    /**
     * @ORM\ManyToOne(targetEntity=ScreenLayout::class, inversedBy="screens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $screenLayout;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity=Playlist::class, inversedBy="screens")
     */
    private $playlists;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getResolutionWidth(): ?int
    {
        return $this->resolutionWidth;
    }

    public function setResolutionWidth(int $resolutionWidth): self
    {
        $this->resolutionWidth = $resolutionWidth;

        return $this;
    }

    public function getResolutionHeight(): ?int
    {
        return $this->resolutionHeight;
    }

    public function setResolutionHeight(int $resolutionHeight): self
    {
        $this->resolutionHeight = $resolutionHeight;

        return $this;
    }

    public function getScreenLayout(): ?ScreenLayout
    {
        return $this->screenLayout;
    }

    public function setScreenLayout(?ScreenLayout $screenLayout): self
    {
        $this->screenLayout = $screenLayout;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection|Playlist[]
     */
    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): self
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists[] = $playlist;
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): self
    {
        $this->playlists->removeElement($playlist);

        return $this;
    }
}
