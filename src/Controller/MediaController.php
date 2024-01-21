<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\MediaUploadType;
use App\Handler\CommentHandler;
use App\Handler\ContactHandler;
use App\Handler\SubscriptionHandler;
use App\Manager\ArticleManager;
use App\Model\MediaUpload;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/media")
 * @Security("is_granted('ROLE_SUPER_ADMIN')")
 */
class MediaController extends AbstractController
{
    /**
     * @Route("/article/{url}/new", name="media_article_new")
     *
     * @param Request        $request        The request.
     * @param Article        $article        The article.
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function articleNewAction(Request $request, Article $article, ArticleManager $articleManager): Response
    {
        $mediaUpload = new MediaUpload();
        $mediaUpload->setArticle($article);
        $form = $this->createForm(MediaUploadType::class, $mediaUpload);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $mediaUpload->getFile()->move(
                    $articleManager->getArticleBasePath() . $article->getDirectory(),
                    $mediaUpload->getName()
                );

                $this->addFlash('success', 'The media has been uploaded successfully.');

                return $this->redirectToRoute('article_edit', [
                    'url' => $article->getUrl(),
                ]);
            }
        }

        return $this->render('app/media/article_new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }
}
