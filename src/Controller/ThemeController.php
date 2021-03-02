<?php
namespace App\Controller;

use App\Entity\Article;
use App\Entity\Theme;
use App\Form\ThemeType;
use App\Manager\ArticleManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/themes")
 */
class ThemeController extends AbstractController
{
    /**
     * @Route("/list", name="theme_list")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @return Response
     */
    public function listAction(): Response
    {
        $themes = $this->getDoctrine()->getRepository(Theme::class)->findAll();

        return $this->render('app/theme/list.html.twig', [
            'themes' => $themes,
        ]);
    }

    /**
     * @Route("/new", name="theme_new")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     *
     * @return Response
     */
    public function newAction(Request $request): Response
    {
        $theme = new Theme();
        $form = $this->createForm(ThemeType::class, $theme);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($theme);
                $em->flush();

                $this->addFlash('success', 'New theme has been added.');

                return $this->redirectToRoute('theme_list');
            }
        }

        return $this->render('app/theme/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{url}", name="theme_edit")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     * @param Theme   $theme   The theme.
     *
     * @return Response
     */
    public function editAction(Request $request, Theme $theme): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($theme);
                $em->flush();

                $this->addFlash('success', 'The theme has been edited.');

                return $this->redirectToRoute('theme_list');
            }
        }

        return $this->render('app/theme/edit.html.twig', [
            'form' => $form->createView(),
            'theme' => $theme,
        ]);
    }

    /**
     * @Route("/delete/{url}", name="theme_delete", methods={"POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     * @param Theme   $theme   The theme.
     *
     * @return Response
     */
    public function deleteAction(Request $request, Theme $theme): Response
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($theme);
        $em->flush();

        $this->addFlash('success', 'The theme has been deleted.');

        return $this->redirectToRoute('theme_list');
    }

    /**
     * @Route("/{url}", name="theme_show")
     *
     * @param Theme          $theme          The theme.
     * @param ArticleManager $articleManager The article manager.
     *
     * @return Response
     * @throws Exception
     */
    public function showAction(Theme $theme, ArticleManager $articleManager): Response
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->getAll([
            'theme' => $theme,
        ], $this->isGranted('ROLE_SUPER_ADMIN'));

        return $this->render('app/theme/show.html.twig', [
            'articles' => $articles,
            'theme' => $theme,
        ]);
    }
}
