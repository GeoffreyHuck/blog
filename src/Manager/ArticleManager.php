<?php
namespace App\Manager;

use App\Model\Article;
use DateTime;
use DOMDocument;
use Exception;
use phpDocumentor\Reflection\Types\Boolean;

class ArticleManager
{
    private $articleBasePath = __DIR__ . '/../../articles/';
    private $publicArticleBasePath = __DIR__ . '/../../public/articles/';

    /**
     * Get an article.
     *
     * @param string  $name             The article name.
     * @param boolean $allowUnpublished Whether to allow the retrieval of unpublished articles.
     *
     * @return Article
     * @throws Exception
     */
    public function get(string $name, $allowUnpublished = false): Article
    {
        $article = new Article();
        $article->setUrl($name);

        $json = $this->getMetadata($name);

        if (!$json['title']) {
            throw new Exception('Metadata should have a title.');
        }
        $article->setTitle($json['title']);

        if (isset($json['category'])) {
            $article->setCategory($json['category']);
        }

        if ($json['published_date']) {
            $publishedDate = new DateTime($json['published_date']);
            $article->setPublishedDate($publishedDate);
        } elseif ($allowUnpublished) {
            $article->setPublishedDate(null);
        } else {
            throw new Exception('The article is not published.');
        }

        $content = $this->getHtmlContent($name);
        $article->setContent($content);

        return $article;
    }

    /**
     * Get all articles.
     *
     * @param array $filters
     * @param bool $allowUnpublished Whether to allow the return of unpublished articles.
     *
     * @return Article[]
     * @throws Exception
     */
    public function getAllWithPreview($filters = [], $allowUnpublished = false): array
    {
        $rawArticles = $this->getRawArticles();

        $articles = [];
        foreach ($rawArticles as $rawArticle) {
            if (!$rawArticle['published_date'] && !$allowUnpublished) {
                continue;
            }
            if (!isset($rawArticle['category'])) {
                continue;
            }
            if (isset($filters['category'])) {
                if (!isset($rawArticle['category'])) {
                    continue;
                }

                $categoryUrl = strtolower($rawArticle['category']);
                $categoryUrl = str_replace(' ', '-', $categoryUrl);
                if ($filters['category'] != $categoryUrl) {
                    continue;
                }
            }

            $article = new Article();

            $article->setUrl($rawArticle['url']);
            $article->setTitle($rawArticle['title']);
            $article->setPublishedDate(new DateTime($rawArticle['published_date']));

            if (isset($rawArticle['category'])) {
                $article->setCategory($rawArticle['category']);
            }

            $article->setContent($rawArticle['preview']);

            $articles[] = $article;
        }

        return $articles;
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

        $metadata = $this->getMetadata($name);
        if (!$metadata['title']) {
            throw new Exception('The metadata JSON must contain a title entry');
        }

        return true;
    }

    /**
     * Build an article.
     *
     * @param string $name The article name.
     * @throws Exception
     */
    public function build(string $name): void
    {
        if (!preg_match('#^[a-zA-Z0-9_\- ]+$#', $name)) {
            throw new Exception('The article name is not valid.');
        }

        // Transform the adoc into html.
        $adocPath = $this->articleBasePath . $name . '/index.adoc';

        $cmd = 'asciidoctor --a imagesdir=/articles/' . $name . ' -s ' . $adocPath;
        shell_exec($cmd);

        // Copy the resources into public directory.
        $validExtensions = ['.jpeg', '.jpg', '.png', '.mp3', '.mp4'];
        $files = scandir($this->articleBasePath . $name);
        foreach ($files as $file) {
            $isValid = false;
            foreach ($validExtensions as $validExtension) {
                if (substr_compare(strtolower($file), $validExtension, -strlen($validExtension)) === 0) {
                    $isValid = true;

                    break;
                }
            }

            if ($isValid) {
                $publicArticleDirectory = $this->publicArticleBasePath . $name;

                if (!file_exists($publicArticleDirectory)) {
                    mkdir($publicArticleDirectory, 0755, true);
                }

                copy($this->articleBasePath . $name . '/' . $file, $publicArticleDirectory . '/' . $file);
            }
        }

        // Update articles.json.
        $articlesJsonPath = $this->articleBasePath . 'articles.json';

        $rawJson = file_get_contents($articlesJsonPath);
        $articles = json_decode($rawJson, true);
        if (!$articles) {
            $articles = [];
        }
        $articles = array_filter($articles, function($article) use ($name) {
            if ($article['url'] == $name) {
                return false;
            }

            return true;
        });
        $articles[] = array_merge($this->getMetadata($name), [
            'url' => $name,
            'preview' => $this->generatePreview($this->getHtmlContent($name)),
        ]);
        usort($articles, function($article1, $article2) {
            return $article1['published_date'] < $article2['published_date'];
        });

        file_put_contents($articlesJsonPath, json_encode($articles));
    }

    /**
     * Get all article names.
     *
     * @return string[]
     */
    public function getAllDirectoryNames(): array
    {
        $files = glob($this->articleBasePath . '*' , GLOB_ONLYDIR);
        $files = array_map(function(string $file) {
            return basename($file);
        }, $files);

        return $files;
    }

    /**
     * Generate a preview of an article content.
     *
     * @param string $content The article content.
     *
     * @return string
     */
    private function generatePreview(string $content): string
    {
        // We want to retrieve <div id="preamble">content</div> from the html content.

        $dom = new DOMDocument();
        $dom->loadHTML($content);

        $preamble = $dom->getElementById('preamble');

        // To get the HTML content.
        if ($preamble) {
            return $preamble->ownerDocument->saveHTML($preamble);
        }

        return '';
    }

    /**
     * Get the metadata of an article.
     *
     * @param string $name The article name.
     *
     * @return array
     * @throws Exception
     */
    private function getMetadata(string $name): array
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

        $metadata = json_decode($rawJson, true);
        if (!$metadata) {
            throw new Exception($path . ' doesn\'t contain valid JSON.');
        }

        return $metadata;
    }

    /**
     * Get all the raw articles.
     *
     * @return array
     * @throws Exception
     */
    private function getRawArticles(): array
    {
        $path = $this->articleBasePath . '/articles.json';

        try {
            $rawJson = file_get_contents($path);
        } catch (Exception $e) {
            $rawJson = false;
        }
        if (!$rawJson) {
            throw new Exception($path . ' doesn\'t exist or is empty.');
        }

        $rawArticles = json_decode($rawJson, true);
        if (!$rawArticles) {
            throw new Exception($path . ' doesn\'t contain valid JSON.');
        }

        return $rawArticles;
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
