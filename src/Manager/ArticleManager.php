<?php
namespace App\Manager;

use App\Entity\Article;
use App\Service\Watermark;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Exception;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ArticleManager
{
    private $articleBasePath = __DIR__ . '/../../articles/';
    private $publicArticleBasePath = __DIR__ . '/../../public/articles/';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /** @var Watermark */
    private $watermark;

    /**
     * ArticleManager constructor.
     *
     * @param EntityManagerInterface $em        The entity manager.
     * @param Watermark              $watermark The watermark service.
     */
    public function __construct(EntityManagerInterface $em, Watermark $watermark)
    {
        $this->em = $em;
        $this->watermark = $watermark;
    }

    /**
     * Load an article.
     *
     * @param string  $directory The article directory.
     * @param Article $article   The article.
     *
     * @throws Exception
     */
    private function load(string $directory, Article $article): void
    {
        $article->setDirectory($directory);

        if (!$article->getUrl()) {
            $article->setUrl($directory);
        }
        if (!$article->getTitle()) {
            $article->setTitle($directory);
        }

        if (!$article->getPublishedAt()) {
            $article->setPublishedAt(null);
        }

        $content = $this->getHtmlContent($directory);
        $article->setContent($content);

        $preview = $this->generatePreview($content);
        $article->setPreview($preview);
    }

    /**
     * Synchronizes all articles from disk.
     *
     * @throws Exception
     */
    public function synchronizeAll(): void
    {

        $directoryNames = $this->getAllDirectoryNames();
        foreach ($directoryNames as $directoryName) {
            $this->synchronize($directoryName);
        }
    }

    /**
     * Synchronizes an article from the disk.
     *
     * @param string $directory The directory.
     * @throws Exception
     */
    public function synchronize(string $directory): void
    {
        $articleRepo = $this->em->getRepository(Article::class);

        $article = $articleRepo->findOneBy([
            'directory' => $directory,
        ]);
        if (!$article) {
            $article = new Article();
        }

        $this->load($directory, $article);

        $this->em->persist($article);
        $this->em->flush();
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
        $adocDir = $this->articleBasePath . $name . '/';

        $cmd = 'asciidoctor -r asciidoctor-diagram -a imagesoutdir=' . $adocDir . ' -a imagesdir=/articles/' . $name . ' -s ' . $adocPath;
        echo $cmd . "\n";
        shell_exec($cmd);

        // Copy the resources into public directory.
        $publicArticleDirectory = $this->publicArticleBasePath . $name;
        if (!file_exists($publicArticleDirectory)) {
            mkdir($publicArticleDirectory, 0777, true);
        }

        /**
         * When the images extensions are in uppercase, they are copied.
         * When they are in lowercase, a watermark is added on it.
         */
        $copyExtensions = ['.mp3', '.mp4', '.JPG', '.PNG', '.JPEG', '.GIF', '.WEBP'];
        $watermarkExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $files = scandir($this->articleBasePath . $name);
        foreach ($files as $file) {
            $fileArticlePath = $this->articleBasePath . $name . '/' . $file;
            $filePublicPath = $publicArticleDirectory . '/' . $file;

            $extension = substr($file, strrpos($file, '.'));

            // Copy assets.
            if (in_array($extension, $copyExtensions)) {
                copy($fileArticlePath, $filePublicPath);
            }

            // Generate watermark.
            if (in_array($extension, $watermarkExtensions)) {
                $this->watermark->generate($fileArticlePath, $filePublicPath);
            }

            // Optimize image.
            if (in_array($extension, $imageExtensions)) {
                $optimizeChain = OptimizerChainFactory::create();

                $optimizeChain->optimize($filePublicPath);
            }
        }
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
