<?php
namespace App\Manager;

use App\Model\Article;
use Exception;
use phpDocumentor\Reflection\Types\Boolean;

class ArticleManager
{
    private $articleBasePath = __DIR__ . '/../../articles/published/';

    /**
     * Get an article.
     *
     * @param string  $name            The article name.
     * @param boolean $allowUnpublished Whether to allow the retrieval of unpublished articles.
     *
     * @return Article
     * @throws Exception
     */
    public function get(string $name, $allowUnpublished = false): Article
    {
        $article = new Article();
        $article->setUrl($name);

        $json = $this->getJsonMetadata($name);

        if (!$json->title) {
            throw new Exception('Metadata should have a title.');
        }
        $article->setTitle($json->title);

        if (!$json->published_date) {
            if ($allowUnpublished) {
                $article->setPublishedDate(null);
            }
        }

        $content = $this->getHtmlContent($name);
        $article->setContent($content);

        return $article;
    }

    /**
     * Validates the source of an article.
     *
     * @param string $name The article name.
     *
     * @return bool Return true or throws an exception.
     * @throws Exception
     */
    public function validateSource(string $name): bool
    {
        if (!preg_match('#^[a-zA-Z0-9-_ ]+$#', $name)) {
            throw new Exception('The name (hence the url) must be formed only of letters, numbers, -, _ or spaces');
        }

        $adocPath = $this->articleBasePath . $name . '/index.adoc';
        if (!file_exists($adocPath)) {
            throw new Exception($adocPath . ' doesn\'t exist or is empty.');
        }

        $json = $this->getJsonMetadata($name);
        if (!$json->title) {
            throw new Exception('The metadata JSON must contain a title entry');
        }

        return true;
    }

    /**
     * Get all article names.
     *
     * @param boolean $allowUnpublished Whether to allow the retrieval of unpublished articles.
     *
     * @return string[]
     */
    public function getAllNames($allowUnpublished = false): array
    {
        $directories = glob($this->articleBasePath . '/*' , GLOB_ONLYDIR);
    }

    public function getArticleBasePath(): string
    {
        return $this->articleBasePath;
    }

    /**
     * Get the json metadata of an article.
     *
     * @param string $name The article name.
     *
     * @return object
     * @throws Exception
     */
    private function getJsonMetadata(string $name): object
    {
        $path = $this->articleBasePath . $name . '/metadata.json';

        try {
            $rawJson = file_get_contents($path);
        } catch (Exception $e) {
            $rawJson = false;
        }
        if (!$rawJson) {
            throw new Exception($path . ' doesn\'t exist or is empty.');
        }

        $json = json_decode($rawJson);
        if (!$json) {
            throw new Exception($path . ' doesn\'t contain valid JSON.');
        }

        return $json;
    }

    /**
     * Gets the content of an article.
     *
     * @param string $name The article name.
     *
     * @return string
     * @throws Exception
     */
    private function getHtmlContent(string $name): string
    {
        $path = $this->articleBasePath . $name . '/index.html';

        try {
            $content = file_get_contents($path);
        } catch (Exception $e) {
            $content = false;
        }
        if (!$content) {
            throw new Exception($path . ' doesn\'t exist or is empty. Did you build this article ?');
        }

        return $content;
    }
}
