<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/articles")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/test")
     *
     * @return Response
     */
    public function articleAction(): Response
    {
        return $this->render('app/articles/article.html.twig');
    }
}
