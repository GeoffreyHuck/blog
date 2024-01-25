<?php

namespace App\Tests\Controller;

use App\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
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

        $em->flush();
    }

    public function testIndexActionIsRedirected()
    {
        $client = static::createClient();

        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(301);
        $this->assertResponseHeaderSame('Location', '/en');
    }

    public function testHomepageActionCanonical()
    {
        $client = static::createClient();
        $this->setupDatabaseFixtures();

        $client->request('GET', '/en');
        $this->assertResponseIsSuccessful();

        // Get the first link with rel="canonical".
        $link = $client->getCrawler()->filter('link[rel="canonical"]')->first();
        $this->assertEquals('http://localhost/en', $link->attr('href'));

        $client->request('GET', '/fr');
        $this->assertResponseIsSuccessful();

        // Get the first link with rel="canonical".
        $link = $client->getCrawler()->filter('link[rel="canonical"]')->first();
        $this->assertEquals('http://localhost/fr', $link->attr('href'));
    }
}
