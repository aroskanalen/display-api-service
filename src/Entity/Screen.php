<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\ScreenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ScreenRepository::class)
 */
class Screen
{
    use EntityIdTrait;
    use EntityTitleDescriptionTrait;
    use EntityModificationTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $size = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $resolutionWidth = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $resolutionHeight = 0;

    /**
     * @ORM\ManyToOne(targetEntity=ScreenLayout::class, inversedBy="screens")
     * @ORM\JoinColumn(nullable=false)
     * @ApiSubresource(maxDepth=1)
     */
    private ScreenLayout $screenLayout;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": ""})
     */
    private string $location = '';

    /**
     * @ORM\ManyToMany(targetEntity=Playlist::class, inversedBy="screens")
     */
    private Collection $playlists;

    /**
     * @ORM\OneToMany(targetEntity=PlaylistScreenRegion::class, mappedBy="screen", orphanRemoval=true)
     */
    private Collection $playlistScreenRegions;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
        $this->playlistScreenRegions = new ArrayCollection();
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getResolutionWidth(): int
    {
        return $this->resolutionWidth;
    }

    public function setResolutionWidth(int $resolutionWidth): self
    {
        $this->resolutionWidth = $resolutionWidth;

        return $this;
    }

    public function getResolutionHeight(): int
    {
        return $this->resolutionHeight;
    }

    public function setResolutionHeight(int $resolutionHeight): self
    {
        $this->resolutionHeight = $resolutionHeight;

        return $this;
    }

    public function getScreenLayout(): ScreenLayout
    {
        return $this->screenLayout;
    }

    public function setScreenLayout(ScreenLayout $screenLayout): self
    {
        $this->screenLayout = $screenLayout;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return ArrayCollection|Playlist[]
     */
    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): self
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists->add($playlist);
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): self
    {
        $this->playlists->removeElement($playlist);

        return $this;
    }

    /**
     * @return ArrayCollection|PlaylistScreenRegion[]
     */
    public function getPlaylistScreenRegions(): Collection
    {
        return $this->playlistScreenRegions;
    }

    public function addPlaylistScreenRegion(PlaylistScreenRegion $playlistScreenRegion): self
    {
        if (!$this->playlistScreenRegions->contains($playlistScreenRegion)) {
            $this->playlistScreenRegions->add($playlistScreenRegion);
            $playlistScreenRegion->setScreen($this);
        }

        return $this;
    }

    public function removePlaylistScreenRegion(PlaylistScreenRegion $playlistScreenRegion): self
    {
        if ($this->playlistScreenRegions->removeElement($playlistScreenRegion)) {
            // set the owning side to null (unless already changed)
            if ($playlistScreenRegion->getScreen() === $this) {
                $playlistScreenRegion->removeScreen();
            }
        }

        return $this;
    }
}
