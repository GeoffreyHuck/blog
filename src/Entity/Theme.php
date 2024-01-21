<?php
namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ThemeRepository::class)
 * @UniqueEntity("url")
 */
class Theme
{
    use OrderableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\ManyToMany(targetEntity=Article::class, mappedBy="themes")
     */
    private $articles;

    /**
     * @ORM\ManyToOne(targetEntity=Language::class, inversedBy="themes")
     */
    private $language;

    /**
     * @ORM\ManyToOne(targetEntity=Article::class)
     */
    private $pageArticle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mainMenuTitle;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->addTheme($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            $article->removeTheme($this);
        }

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

    public function getPageArticle(): ?Article
    {
        return $this->pageArticle;
    }

    public function setPageArticle(?Article $pageArticle): self
    {
        $this->pageArticle = $pageArticle;

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
}
