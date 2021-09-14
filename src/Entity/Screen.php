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
    use EntityTitleDescriptionTrait;
    use EntityModificationTrait;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $size = 0;

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
     */
    private ScreenLayout $screenLayout;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": ""})
     */
    private string $location = '';

    /**
     * @ORM\ManyToMany(targetEntity=Playlist::class, inversedBy="screens")
     */
    private ArrayCollection $playlists;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
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
    public function getPlaylists(): ArrayCollection
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
}
