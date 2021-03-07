<?php
namespace App\Manager;

use App\Entity\Article;
use App\Service\Watermark;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Exception;
use Imagick;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ArticleManager
{
    const MATHEMATICAL_FONT_SIZE_RATIO = 1.3;

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

        $coverFilePath = $this->publicArticleBasePath . $directory . '/cover.JPG';
        if (file_exists($coverFilePath)) {
            $image = new Imagick($coverFilePath);

            $article->setCoverWidth($image->getImageWidth());
            $article->setCoverHeight($image->getImageHeight());
        } else {
            $article->setCoverWidth(null);
            $article->setCoverHeight(null);
        }
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

        $articlePath = $this->articleBasePath . $name;

        // Transform the adoc into html.
        $adocPath = $articlePath . '/index.adoc';

        $cmd = 'asciidoctor ' .
            '-r asciidoctor-mathematical ' .
            '-r asciidoctor-diagram ' .
            '-a mathematical-ppi=' . (72 * self::MATHEMATICAL_FONT_SIZE_RATIO) . ' ' .
            '-a outdir=articles ' .
            '-a imagesdir=' . $name . ' ' .
            '-s ' . $adocPath;
        echo $cmd . "\n";
        shell_exec($cmd);

        /**
         * Resize the mathematical formulas' <img> width and height.
         */
        $htmlPath = $articlePath  . '/index.html';

        $resizeRatio = self::MATHEMATICAL_FONT_SIZE_RATIO;
        $htmlContent = file_get_contents($htmlPath);

        $htmlContent = preg_replace_callback('#<img.*src=".*stem-.*".*width="(\d+)".*height="(\d+)">#U', function ($matches) use ($resizeRatio) {
            $newWidth = round($matches[1] * $resizeRatio);
            $newHeight = round($matches[2] * $resizeRatio);

            $result = $matches[0];
            $result = str_replace('width="' . $matches[1] . '"', 'width="' . $newWidth . '"', $result);
            $result = str_replace('height="' . $matches[2] . '"', 'height="' . $newHeight . '"', $result);

            return $result;
        }, $htmlContent);

        file_put_contents($htmlPath, $htmlContent);

        // Copy the resources into public directory.
        $publicArticleDirectory = $this->publicArticleBasePath . $name;
        if (!file_exists($publicArticleDirectory)) {
            mkdir($publicArticleDirectory, 0777, true);
        }

        /**
         * When the images extensions are in uppercase, they are copied.
         * When they are in lowercase, a watermark is added on it (if it's not a math formula).
         */
        $copyExtensions = ['.mp3', '.mp4', '.JPG', '.PNG', '.JPEG', '.GIF', '.WEBP'];
        $watermarkExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

        $inDirFiles = scandir($articlePath);
        $files = [];
        foreach ($inDirFiles as $inDirFile) {
            $files[] = $this->articleBasePath . $name . '/' . $inDirFile;
        }

        // Math formulas are generated in a subdir of the same name.
        $articleMathSubDir = $articlePath . '/' . $name;
        if (file_exists($articleMathSubDir)) {
            $inSubDirFiles = scandir($articleMathSubDir);
            foreach ($inSubDirFiles as $inSubDirFile) {
                $files[] = $articleMathSubDir . '/' . $inSubDirFile;
            }
        }

        foreach ($files as $file) {
            $filename = basename($file);

            $isMath = (substr($filename, 0, 5) == 'stem-');

            $filePublicPath = $publicArticleDirectory . '/' . $filename;

            $extension = substr($filename, strrpos($filename, '.'));

            if ($filename == 'cover.JPG') {
                copy($file, $publicArticleDirectory . '/cover_original.JPG');
            }

            // Copy assets.
            if (in_array($extension, $copyExtensions) || (in_array($extension, $watermarkExtensions) && $isMath)) {
                copy($file, $filePublicPath);
            }

            // Generate watermark.
            if (in_array($extension, $watermarkExtensions) && !$isMath) {
                $this->watermark->generate($file, $filePublicPath);
            }

            // Treatments on images.
            if (in_array(strtolower($extension), $imageExtensions) && !$isMath) {
                // Maximum width.
                $image = new Imagick($filePublicPath);

                if ($image->getImageWidth() > 1080) {
                    $image->adaptiveResizeImage(1080, (1080 * $image->getImageHeight()) / $image->getImageWidth());
                    $image->writeImage();
                }

                // Optimize.
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
