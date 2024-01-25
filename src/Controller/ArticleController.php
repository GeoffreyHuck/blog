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
 * @Route("/{_locale}/articles", requirements={"_locale": "en|fr"})
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/new", name="article_new")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request        $request        The request.
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function newAction(Request $request, ArticleManager $articleManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /**
                 * The directory in which we build the article.
                 * We set it as the first url.
                 * This way, if the url changes, the directory will not change.
                 */
                $article->setDirectory($article->getUrl());

                $articleManager->build($article->getDirectory(), $article->getUrl(), $article->getRawContent());
                $articleManager->synchronize($article);

                $em = $this->getDoctrine()->getManager();

                $em->persist($article);
                $em->flush();

                $this->addFlash('success', 'The article has been edited.');

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('app/article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/edit/{url}", name="article_edit")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request        $request        The request.
     * @param ArticleManager $articleManager The article manager.
     * @param Article        $article        The article.
     *
     * @return Response
     * @throws Exception
     */
    public function editAction(Request $request, ArticleManager $articleManager, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $articleManager->build($article->getDirectory(), $article->getUrl(), $article->getRawContent());
                $articleManager->synchronize($article);

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
     * Generates the preview of an article.
     *
     * @Route("/generate_preview/{url}", name="article_generate_preview")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request        $request        The request.
     * @param Article        $article        The article.
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function generatePreviewAction(Request $request, Article $article, ArticleManager $articleManager): Response
    {
        $testDirectory = 'test_preview';

        $rawContent = $request->query->get('rawContent', '');

        $articleManager->copyMediaToTestDirectory($article->getDirectory(), $testDirectory);
        $articleManager->build($testDirectory, $testDirectory, $rawContent);
        $html = $articleManager->getHtmlContent($testDirectory);

        return new Response($html);
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
        ContactHandler $contactHandler
    ): Response
    {
        if ($article->getLanguage()->getCode() != $request->getLocale()) {
            throw $this->createNotFoundException('Locale does not match');
        }
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
        if ($article->getUrl() == 'about-me' || $article->getUrl() == 'consultant-developpeur-informatique') {
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

            $this->addFlash('success', 'Le status a bien été mis à jour vers : ' . $newStatus);
        } else {
            $this->addFlash('danger', 'Cannot set status ' . $newStatus . ' to comment ' . $comment->getId());
        }

        return $this->redirect($comment->getUrl() . '?deleted=1#' . $comment->getAnchor());
    }

    /**
     * @Route("/delete/{url}", name="article_delete", methods={"POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Article $article The article.
     *
     * @return Response
     */
    public function deleteAction(Article $article): Response
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'The article has been deleted.');

        return $this->redirectToRoute('homepage');
    }
}
