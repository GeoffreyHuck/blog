<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @UniqueEntity("url")
 */
class Article
{
    // Order when in menu.
    use OrderableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $preview;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToMany(targetEntity=Theme::class, inversedBy="articles")
     */
    private $themes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $directory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coverWidth;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coverHeight;

    /**
     * @ORM\ManyToOne(targetEntity=Language::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=true)
     */
    private $language;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $inMainMenu = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mainMenuTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $rawContent;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublishedAt(): ?DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): self
    {
        $this->preview = $preview;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection|Theme[]
     */
    public function getThemes(): Collection
    {
        return $this->themes;
    }

    public function addTheme(Theme $theme): self
    {
        if (!$this->themes->contains($theme)) {
            $this->themes[] = $theme;
        }

        return $this;
    }

    public function removeTheme(Theme $theme): self
    {
        $this->themes->removeElement($theme);

        return $this;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function getCoverWidth(): ?int
    {
        return $this->coverWidth;
    }

    public function setCoverWidth(?int $coverWidth): self
    {
        $this->coverWidth = $coverWidth;

        return $this;
    }

    public function getCoverHeight(): ?int
    {
        return $this->coverHeight;
    }

    public function setCoverHeight(?int $coverHeight): self
    {
        $this->coverHeight = $coverHeight;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getInMainMenu(): ?bool
    {
        return $this->inMainMenu;
    }

    public function setInMainMenu(?bool $inMainMenu): self
    {
        $this->inMainMenu = $inMainMenu;

        return $this;
    }

    public function getMainMenuTitle(): ?string
    {
        return $this->mainMenuTitle;
    }

    public function setMainMenuTitle(?string $mainMenuTitle): self
    {
        $this->mainMenuTitle = $mainMenuTitle;

        return $this;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    public function setRawContent(?string $rawContent): self
    {
        $this->rawContent = $rawContent;

        return $this;
    }
}
