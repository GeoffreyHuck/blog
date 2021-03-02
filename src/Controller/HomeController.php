<?php
namespace App\Controller;

use App\Entity\Article;
use App\Manager\ArticleManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Renders the homepage.
     *
     * @Route("/", name="homepage")
     *
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function homepageAction(ArticleManager $articleManager): Response
    {
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $articleRepo->getAll([], $this->isGranted('ROLE_SUPER_ADMIN'));

        return $this->render('app/home/homepage.html.twig', [
            'articles' => $articles,
        ]);
    }
}
