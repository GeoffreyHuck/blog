<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Handler\CommentHandler;
use App\Manager\ArticleManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/articles")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/{url<^[a-zA-Z0-9-_ ]+$>}", name="article_show")
     *
     * @param Request        $request        The request.
     * @param string         $url            The article url.
     * @param ArticleManager $articleManager The article manager.
     * @param CommentHandler $commentHandler The comment handler.
     *
     * @return Response
     */
    public function showAction(Request $request, string $url, ArticleManager $articleManager, CommentHandler $commentHandler): Response
    {
        try {
            $article = $articleManager->get($url);
        } catch (Exception $e) {
            throw $this->createNotFoundException($e);
        }

        $articleUrl = $this->generateUrl('article_show', [
            'url' => $url,
        ]);

        if ($article->getCategory()) {
            $commentHandler->setUrl($articleUrl);
            if ($commentHandler->processRequest($request)) {
                return $this->redirect($request->getRequestUri());
            }
        }

        $commentRepo = $this->getDoctrine()->getRepository(Comment::class);

        $getSpam = false;
        if ($this->isGranted('ROLE_SUPER_ADMIN') && $request->query->get('verify', false)) {
            $getSpam = true;
        }

        $comments = $commentRepo->getForUrl($articleUrl, $getSpam);

        return $this->render('app/article/show.html.twig', array_merge([
            'article' => $article,
            'comments' => $comments,
        ], $commentHandler->getViewParameters()));
    }
}
