<?php

namespace App\Model;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class MediaUpload
{
    private Article $article;

    /**
     * @var string The name of the media with the extension.
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*$/",
     *     message="The name of the media must only contain letters, numbers and dashes."
     * )
     * @Assert\Regex(
     *     pattern="/\.(pdf|jpg|jpeg|png|PDF|JPG|JPEG|PNG)$/",
     *     message="The name of the media must end with .pdf, .jpg, .jpeg or .png."
     * )
     */
    private string $name;

    /**
     * @var File The file.
     *
     * @Assert\NotBlank()
     * @Assert\File(
     *     maxSize="8128k",
     *     mimeTypes={
     *       "application/pdf",
     *       "application/x-pdf",
     *       "image/jpeg",
     *       "image/png"
     *     },
     *     mimeTypesMessage="Please upload a valid file (pdf or image)"
     * )
     */
    private File $file;

    /**
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * @param Article $article
     *
     * @return MediaUpload
     */
    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return MediaUpload
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return MediaUpload
     */
    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
