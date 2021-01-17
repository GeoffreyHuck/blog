<?php
namespace App\Twig;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('slugify', [$this, 'slugify']),
        ];
    }

    /**
     * Slugify a string.
     *
     * @param string $value The string.
     *
     * @return string
     */
    public function slugify(string $value): string
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug($value);
    }
}
