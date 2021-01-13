<?php
namespace App\Controller;

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
        $articlePreviews = $articleManager->getAllWithPreview();

        return $this->render('app/home/homepage.html.twig', [
            'articlePreviews' => $articlePreviews,
        ]);
    }
}
