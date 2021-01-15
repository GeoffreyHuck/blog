<?php
namespace App\Controller;

use App\Manager\ArticleManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/articles")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/{url<^[a-zA-Z0-9-_ ]+$>}", name="article_show")
     *
     * @param string         $url            The article url.
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     */
    public function showAction(string $url, ArticleManager $articleManager): Response
    {
        try {
            $article = $articleManager->get($url);
        } catch (Exception $e) {
            throw $this->createNotFoundException($e);
        }

        return $this->render('app/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{url<^[a-zA-Z0-9-_ ]+$>}/{image<^[a-zA-Z0-9-_ ]+\.(png|jpg|png)$>}", name="article_image")
     *
     * @param string $url   The article url.
     * @param string $image The image name.
     *
     * @return Response
     */
    public function imageAction(string $url, string $image): Response
    {
        $filename = __DIR__ . '/../../articles/' . $url . '/' . $image;
        if (!file_exists($filename)) {
            throw $this->createNotFoundException('This image doesn\'t exist.');
        }

        $explodedFilename = explode('.', $image);

        $ext = strtolower(array_pop($explodedFilename));
        if ($ext == 'png') {
            $mimeType = 'image/png';

            $sourceImage = imagecreatefrompng($filename);
        } elseif ($ext == 'jpg' || $ext == 'jpeg') {
            $mimeType = 'image/jpeg';

            $sourceImage = imagecreatefromjpeg($filename);
        } else {
            throw $this->createNotFoundException('File extension is not an image : ' . $image);
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $destinationWidth = $sourceWidth;
        $destinationHeight = $sourceHeight;

        $imageWithWaterMark = imagecreatetruecolor($destinationWidth, $destinationHeight);
        imagecopyresampled($imageWithWaterMark, $sourceImage, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);

        // Add watermark.
        $fontNotoSerif = __DIR__ . '/../../public/fonts/NotoSerif-Regular.ttf';

        $fontSize = (13);
        $fontColor = imagecolorallocate($imageWithWaterMark, 186, 57, 37);

        $textX = ($destinationWidth - 175);
        $textY = ($sourceHeight - 10);

        imagettftext($imageWithWaterMark, $fontSize, 0, $textX, $textY, $fontColor, $fontNotoSerif, '© geoffreyhuck.com');

        ob_start();
        if ($mimeType == 'image/png') {
            imagepng($imageWithWaterMark);
        } else {
            imagejpeg($imageWithWaterMark);
        }
        $destinationImage = ob_get_clean();

        $destinationFile = __DIR__ . '/../../public/articles/' . $url . '/' . $image;

        file_put_contents($destinationFile, $destinationImage);

        imagedestroy($sourceImage);
        imagedestroy($imageWithWaterMark);

        $response = new Response($destinationImage);
        $response->headers->add([
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'filename="' . $image . '"',
        ]);
        $response->send();

        return $response;
    }
}
