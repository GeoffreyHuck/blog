<?php

namespace App\Tests\Controller;

use App\Entity\Language;
use App\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ThemeControllerTest extends WebTestCase
{
    public function setupDatabaseFixtures()
    {
        $em = self::$container->get('doctrine.orm.entity_manager');

        $languageEN = new Language();
        $languageEN->setCode('en');
        $languageEN->setName('English');
        $em->persist($languageEN);

        $languageFR = new Language();
        $languageFR->setCode('fr');
        $languageFR->setName('Français');
        $em->persist($languageFR);

        $theme = new Theme();
        $theme->setUrl('theme');
        $theme->setLanguage($languageFR);
        $theme->setName('Test');
        $em->persist($theme);

        $em->flush();
    }

    public function testShowActionCanonical()
    {
        $client = static::createClient();
        $this->setupDatabaseFixtures();

        $client->request('GET', '/fr/themes/theme');
        $this->assertResponseIsSuccessful();

        // Get the first link with rel="canonical".
        $link = $client->getCrawler()->filter('link[rel="canonical"]')->first();
        $this->assertEquals('http://localhost/fr/themes/theme', $link->attr('href'));
    }

    public function testShowActionLocaleMustMatchTheme()
    {
        $client = static::createClient();
        $this->setupDatabaseFixtures();

        $client->request('GET', '/fr/themes/theme');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/en/themes/theme');
        $this->assertResponseStatusCodeSame(404);
    }
}
