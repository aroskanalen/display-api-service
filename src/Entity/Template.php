<?php

namespace App\Entity;

use App\Repository\TemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 */
class Template
{
    use EntityIdTrait;
    use EntityTitleDescriptionTrait;
    use EntityModificationTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default" : ""})
     */
    private string $icon = '';

    /**
     * @ORM\Column(type="array")
     */
    private array $resources = [];

    /**
     * @ORM\OneToMany(targetEntity=Slide::class, mappedBy="template")
     */
    private ArrayCollection $slides;

    public function __construct()
    {
        $this->slides = new ArrayCollection();
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function setResources(array $resources): self
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * @return ArrayCollection|Slide[]
     */
    public function getSlides(): ArrayCollection
    {
        return $this->slides;
    }

    public function addSlide(Slide $slide): self
    {
        if (!$this->slides->contains($slide)) {
            $this->slides->add($slide);
            $slide->setTemplate($this);
        }

        return $this;
    }

    public function removeSlide(Slide $slide): self
    {
        if ($this->slides->removeElement($slide)) {
            // set the owning side to null (unless already changed)
            if ($slide->getTemplate() === $this) {
                $slide->removeTemplate();
            }
        }

        return $this;
    }
}
