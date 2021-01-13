<?php
namespace App\Model;

use DateTime;

class Article
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $url;

    /**
     * @var DateTime
     */
    private $publishedDate;

    /**
     * Whether the article is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return (bool)$this->publishedDate;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPublishedDate(): ?DateTime
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(?DateTime $publishedDate): self
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }
}
