<?php
namespace App\Controller;

use App\Entity\Article;
use App\Manager\ArticleManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Renders the homepage.
     *
     * @Route("/{_locale}", name="homepage", requirements={"_locale": "fr"})
     *
     * @param string  $_locale The locale.
     *
     * @return Response
     * @throws Exception
     */
    public function homepageAction(string $_locale = 'en'): Response
    {
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $articleRepo->getAll([
            'language' => $_locale,
        ], $this->isGranted('ROLE_SUPER_ADMIN'));

        return $this->render('app/home/homepage.html.twig', [
            'articles' => $articles,
        ]);
    }
}
