<?php
namespace App\Controller;

use App\Manager\ArticleManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/categories")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/{url<[a-zA-Z0-9-_ ]+>}", name="category_show")
     *
     * @param string         $url            The url.
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function showAction(string $url, ArticleManager $articleManager): Response
    {
        $articlePreviews = $articleManager->getAllWithPreview([
            'category' => $url,
        ]);
        if (!$articlePreviews) {
            throw $this->createNotFoundException('This category doesn\'t exist.');
        }

        return $this->render('app/category/show.html.twig', [
            'articlePreviews' => $articlePreviews,
            'name' => $articlePreviews[0]->getCategory(),
        ]);
    }
}
