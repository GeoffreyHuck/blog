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
     * Index redirects to the default locale (en).
     *
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        return $this->redirectToRoute('homepage', [
            '_locale' => 'en',
        ], 301);
    }

    /**
     * Renders the homepage.
     *
     * @Route("/{_locale}", name="homepage", requirements={"_locale": "en|fr"})
     *
     * @param Request $request The request.
     *
     * @return Response
     * @throws Exception
     */
    public function homepageAction(Request $request): Response
    {
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $articleRepo->getAll([
            'language' => $request->getLocale(),
        ], $this->isGranted('ROLE_SUPER_ADMIN'));

        return $this->render('app/home/homepage.html.twig', [
            'articles' => $articles,
        ]);
    }
}
