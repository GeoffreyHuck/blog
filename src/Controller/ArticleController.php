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
     * @Route("/{url<[a-zA-Z0-9-_ ]+>}", name="article_show")
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
}
