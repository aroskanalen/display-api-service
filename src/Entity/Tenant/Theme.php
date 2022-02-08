<?php

namespace App\Entity\Tenant;

use App\Entity\EntityIdTrait;
use App\Entity\EntityModificationTrait;
use App\Entity\EntityTitleDescriptionTrait;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ThemeRepository::class)
 * @ORM\EntityListeners({"App\EventListener\ThemeDoctrineEventListener"})
 */
class Theme extends AbstractTenantScopedEntityScoped
{
    use EntityIdTrait;
    use EntityTitleDescriptionTrait;
    use EntityModificationTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="text")
     */
    private string $cssStyles = '';

    /**
     * @ORM\OneToMany(targetEntity=Slide::class, mappedBy="theme")
     */
    private Collection $slides;

    public function __construct()
    {
        $this->slides = new ArrayCollection();
    }

    public function getCssStyles(): string
    {
        return $this->cssStyles;
    }

    public function setCssStyles(string $cssStyles): self
    {
        $this->cssStyles = $cssStyles;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSlides(): Collection
    {
        return $this->slides;
    }

    public function addSlide(Slide $slide): self
    {
        if (!$this->slides->contains($slide)) {
            $this->slides[] = $slide;
            $slide->setTheme($this);
        }

        return $this;
    }

    public function removeSlide(Slide $slide): self
    {
        if ($this->slides->removeElement($slide)) {
            // set the owning side to null (unless already changed)
            if ($slide->getTheme() === $this) {
                $slide->setTheme(null);
            }
        }

        return $this;
    }
}
