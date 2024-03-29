<?php
namespace App\Utils;

/**
 * Class HtmlHelper
 * @package App\Helper
 *
 * Provides methods to adapt the HTML generated by asciidoctor.
 */
class HtmlHelper
{
    /**
     * Add prefix to built image src to get the right path.
     *
     * @param string $html
     * @return string
     */
    public static function fixBuiltImageSrc(string $html): string
    {
        // Add "/articles/" in front of image src that are relative.
        return preg_replace_callback(
            '/<img src="([^"]+)"/',
            function ($matches) {
                if (strpos($matches[1], 'http://') === 0 || strpos($matches[1], 'https://') === 0) {
                    return $matches[0];
                }
                if (strpos($matches[1], '/') !== 0) {
                    return '<img src="/articles/' . $matches[1] . '"';
                }

                return $matches[0];
            },
            $html
        );
    }
}
