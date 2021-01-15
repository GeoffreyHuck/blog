<?php
namespace App\Service;

use Exception;

class Watermark
{
    /**
     * Generates a watermark and store it in the public directory.
     *
     * @param string $source The source image.
     * @param string $destination The destination image.
     *
     * @throws Exception
     */
    public function generate(string $source, string $destination): void
    {
        $explodedFilename = explode('.', $source);

        $ext = strtolower(array_pop($explodedFilename));
        if ($ext == 'png') {
            $mimeType = 'image/png';

            $sourceImage = imagecreatefrompng($source);
        } elseif ($ext == 'jpg' || $ext == 'jpeg') {
            $mimeType = 'image/jpeg';

            $sourceImage = imagecreatefromjpeg($source);
        } else {
            throw new Exception('File extension is not an image : ' . $source);
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        $destinationWidth = $sourceWidth;
        $destinationHeight = $sourceHeight;

        $imageWithWaterMark = imagecreatetruecolor($destinationWidth, $destinationHeight);
        if ($ext == 'png') {
            $background = imagecolorallocate($imageWithWaterMark , 0, 0, 0);

            imagecolortransparent($imageWithWaterMark, $background);
            imagealphablending($imageWithWaterMark, false);
            imagesavealpha($imageWithWaterMark, true);
        }

        imagecopyresampled($imageWithWaterMark, $sourceImage, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);

        // Add watermark.
        $fontNotoSerif = __DIR__ . '/../../public/fonts/NotoSerif-Regular.ttf';

        $widthRatio = ($destinationWidth / 640);
        $fontSize = (10 * $widthRatio);
        $fontColor = imagecolorallocate($imageWithWaterMark, 186, 57, 37);

        $textX = ($destinationWidth - (165 * $widthRatio));
        $textY = ($sourceHeight - 10);

        imagettftext($imageWithWaterMark, $fontSize, 0, $textX, $textY, $fontColor, $fontNotoSerif, '© geoffreyhuck.com');

        ob_start();
        if ($mimeType == 'image/png') {
            imagepng($imageWithWaterMark);
        } else {
            imagejpeg($imageWithWaterMark);
        }
        $destinationImage = ob_get_clean();

        file_put_contents($destination, $destinationImage);

        imagedestroy($sourceImage);
        imagedestroy($imageWithWaterMark);
    }
}
