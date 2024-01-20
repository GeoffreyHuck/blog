<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Language;
use App\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NavigationController extends AbstractController
{
    public function renderNav(Request $request): Response
    {
        $languages = $this->getDoctrine()->getRepository(Language::class)->findAll();

        $themes = $this->getDoctrine()->getRepository(Theme::class)->findForMenu($request->getLocale());
        $articles = $this->getDoctrine()->getRepository(Article::class)->findForMenu($request->getLocale());

        return $this->render('app/navigation/renderNav.html.twig', [
            'languages' => $languages,
            'themes' => $themes,
            'articles' => $articles,
        ]);
    }
}
