<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Handler\CommentHandler;
use App\Handler\ContactHandler;
use App\Handler\SubscriptionHandler;
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
     * @Route("/sync", name="article_sync")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function syncAction(ArticleManager $articleManager): Response
    {
        $articleManager->synchronizeAll();

        $this->addFlash('success', 'The articles have been synchronized.');

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/edit/{url}", name="article_edit")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     * @param Article $article The article.
     *
     * @return Response
     */
    public function editAction(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($article);
                $em->flush();

                $this->addFlash('success', 'The article has been edited.');

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('app/article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{url}", name="article_show")
     *
     * @param Request             $request             The request.
     * @param Article             $article             The article.
     * @param CommentHandler      $commentHandler      The comment handler.
     * @param SubscriptionHandler $subscriptionHandler The subscription handler.
     * @param ContactHandler      $contactHandler      The contact handler.
     *
     * @return Response
     */
    public function showAction(
        Request $request,
        Article $article,
        CommentHandler $commentHandler,
        SubscriptionHandler $subscriptionHandler,
        ContactHandler $contactHandler): Response
    {
        if (!$article->getPublishedAt() && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createNotFoundException('Not published');
        }

        $articleUrl = $this->generateUrl('article_show', [
            'url' => $article->getUrl(),
        ]);

        if (count($article->getThemes()) > 0) {
            $commentHandler->setUrl($articleUrl);
            if ($commentHandler->processRequest($request)) {
                return $this->redirect($request->getRequestUri());
            }

            if ($subscriptionHandler->processRequest($request)) {
                return $this->redirect($request->getRequestUri());
            }
        }
        if ($article->getUrl() == 'about-me') {
            if ($contactHandler->processRequest($request)) {
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
        ], $commentHandler->getViewParameters(),
            $subscriptionHandler->getViewParameters(),
            $contactHandler->getViewParameters()
        ));
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

            $this->addFlash('success', 'Le status a bien ??t?? mis ?? jour vers : ' . $newStatus);
        } else {
            $this->addFlash('danger', 'Cannot set status ' . $newStatus . ' to comment ' . $comment->getId());
        }

        return $this->redirect($comment->getUrl() . '?deleted=1#' . $comment->getAnchor());
    }

    /**
     * @Route("/delete/{url}", name="article_delete", methods={"POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     * @param Article $article The article.
     *
     * @return Response
     */
    public function deleteAction(Request $request, Article $article): Response
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'The article has been deleted.');

        return $this->redirectToRoute('homepage');
    }
}
