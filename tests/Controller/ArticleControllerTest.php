<?php

namespace App\Tests\Controller;

use App\Entity\Article;
use App\Entity\Language;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleControllerTest extends WebTestCase
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

        $unpublishedArticle = new Article();
        $unpublishedArticle->setUrl('unpublished');
        $unpublishedArticle->setLanguage($languageEN);
        $unpublishedArticle->setTitle('Test');
        $unpublishedArticle->setContent('content');
        $unpublishedArticle->setDirectory('test');
        $em->persist($unpublishedArticle);

        $article = new Article();
        $article->setUrl('test');
        $article->setLanguage($languageFR);
        $article->setTitle('Test');
        $article->setContent('content');
        $article->setDirectory('test');
        $article->setPublishedAt(new DateTime());
        $em->persist($article);

        $em->flush();
    }

    public function testShowActionMustBePublished()
    {
        $client = static::createClient();
        $this->setupDatabaseFixtures();

        $client->request('GET', '/en/articles/unpublished');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowActionLocaleMustMatchArticle()
    {
        $client = static::createClient();
        $this->setupDatabaseFixtures();

        $client->request('GET', '/fr/articles/test');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/en/articles/test');
        $this->assertResponseStatusCodeSame(404);
    }
}
