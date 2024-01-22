<?php
namespace App\Manager;

use App\Entity\Article;
use App\Service\Watermark;
use App\Utils\HtmlHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Imagick;
use Masterminds\HTML5;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ArticleManager
{
    const MATHEMATICAL_FONT_SIZE_RATIO = 1.3;

    private string $articleBasePath = __DIR__ . '/../../articles/';
    private string $publicArticleBasePath = __DIR__ . '/../../public/articles/';

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /** @var Watermark */
    private Watermark $watermark;

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
     * Gets the article base path.
     *
     * @return string
     */
    public function getArticleBasePath(): string
    {
        return $this->articleBasePath;
    }

    /**
     * Synchronizes an article from the disk.
     *
     * @param Article $article The article.
     * @throws Exception
     */
    public function synchronize(Article $article): void
    {
        $content = $this->getHtmlContent($article->getDirectory());
        $article->setContent($content);

        $preview = $this->generatePreview($content);
        $article->setPreview($preview);

        $coverFilePath = $this->publicArticleBasePath . $article->getUrl() . '/cover.JPG';
        if (file_exists($coverFilePath)) {
            $image = new Imagick($coverFilePath);

            $article->setCoverWidth($image->getImageWidth());
            $article->setCoverHeight($image->getImageHeight());
        } else {
            $article->setCoverWidth(null);
            $article->setCoverHeight(null);
        }

        $this->em->persist($article);
        $this->em->flush();
    }

    /**
     * Build an article.
     * Generates the html content from the asciidoc content.
     * Copies the resources into the public directory.
     * Resizes the mathematical formulas.
     * Adds a watermark on the images.
     * Optimizes the images.
     *
     * @param string $sourceDirectory The source directory.
     * @param string $publicDirectory The public directory.
     * @param string $asciiDoc        The asciidoc content.
     *
     * @throws Exception
     */
    public function build(string $sourceDirectory, string $publicDirectory, string $asciiDoc): void
    {
        $articlePath = $this->articleBasePath . $sourceDirectory;
        if (!file_exists($articlePath)) {
            mkdir($articlePath, 0755);
        }

        // Put the asciidoc content into a file.
        $adocPath = $articlePath . '/index.adoc';
        file_put_contents($adocPath, $asciiDoc);

        // Transform the adoc into html.
        $cmd = 'asciidoctor ' .
            '-r asciidoctor-mathematical ' .
            '-r asciidoctor-diagram ' .
            '-a mathematical-ppi=' . (72 * self::MATHEMATICAL_FONT_SIZE_RATIO) . ' ' .
            '-a outdir=articles ' .
            '-a imagesdir=' . $sourceDirectory . ' ' .
            '-s ' . $adocPath;

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

        // Fix the built image src.
        $htmlContent = HtmlHelper::fixBuiltImageSrc($htmlContent);

        // Update the html file.
        file_put_contents($htmlPath, $htmlContent);

        // Copy the resources into public directory.
        $publicArticleDirectory = $this->publicArticleBasePath . $publicDirectory;
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
            $files[] = $this->articleBasePath . $sourceDirectory . '/' . $inDirFile;
        }

        // Math formulas are generated in a subdir of the same name.
        $articleMathSubDir = $articlePath . '/' . $sourceDirectory;
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
     * Generate a preview of an article content.
     *
     * @param string $content The article content.
     *
     * @return string
     */
    private function generatePreview(string $content): string
    {
        // We want to retrieve <div id="preamble">content</div> from the html content.

        $html5 = new HTML5();
        $dom = $html5->loadHTML($content);

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
     * @param string $sourceDirectory The source directory.
     *
     * @return string
     * @throws Exception
     */
    public function getHtmlContent(string $sourceDirectory): string
    {
        $path = $this->articleBasePath . $sourceDirectory . '/index.html';

        try {
            $content = file_get_contents($path);
        } catch (Exception $e) {
            $content = false;
        }
        if (!$content) {
            throw new Exception($path . ' does\'t exist or is empty. Did you build this article ?');
        }

        return $content;
    }

    /**
     * Copy the media of an article to the test directory.
     *
     * @param string $sourceDirectory The source directory.
     * @param string $testDirectory   The test directory.
     *
     * @return void
     */
    public function copyMediaToTestDirectory(string $sourceDirectory, string $testDirectory): void
    {
        $sourcePath = $this->articleBasePath . $sourceDirectory;
        $testPath = $this->articleBasePath . $testDirectory;

        if (!file_exists($testPath)) {
            mkdir($testPath, 0755, true);
        }

        $files = scandir($sourcePath);
        foreach ($files as $file) {
            if ($file == 'index.adoc' || $file == 'index.html') {
                continue;
            }

            $sourceFilePath = $sourcePath . '/' . $file;
            $testFilePath = $testPath . '/' . $file;

            if (is_file($sourceFilePath)) {
                copy($sourceFilePath, $testFilePath);
            }
        }
    }
}
