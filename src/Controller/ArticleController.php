<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Handler\CommentHandler;
use App\Manager\ArticleManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

        $commentStatuses = [Comment::STATUS_NEW, Comment::STATUS_NOTIFIED, Comment::STATUS_VERIFIED];
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            if ($request->query->get('deleted', false)) {
                $commentStatuses = [];
            } else {
                $commentStatuses[] = Comment::STATUS_MANUAL;
            }
        }

        $comments = $commentRepo->getForUrl($articleUrl, $commentStatuses);

        return $this->render('app/article/show.html.twig', array_merge([
            'article' => $article,
            'comments' => $comments,
        ], $commentHandler->getViewParameters()));
    }

    /**
     * Updates the status of a comment.
     *
     * @Route("/update_status/{id}", name="article_update_status", methods={"POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     * @param Comment $comment The comment.
     *
     * @return Response
     */
    public function updateStatusAction(Request $request, Comment $comment): Response
    {
        $newStatus = $request->request->get('status', '');
        if (in_array($newStatus, $comment->getPossibleStatuses())) {
            $comment->setStatus($newStatus);

            $em = $this->getDoctrine()->getManager();

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Le status a bien été mis à jour vers : ' . $newStatus);
        } else {
            $this->addFlash('danger', 'Cannot set status ' . $newStatus . ' to comment ' . $comment->getId());
        }

        return $this->redirect($comment->getUrl() . '?deleted=1#' . $comment->getAnchor());
    }
}
