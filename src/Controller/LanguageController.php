<?php
namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/languages")
 */
class LanguageController extends AbstractController
{
    /**
     * @Route("/list", name="language_list")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @return Response
     */
    public function listAction(): Response
    {
        $languages = $this->getDoctrine()->getRepository(Language::class)->findAll();

        return $this->render('app/language/list.html.twig', [
            'languages' => $languages,
        ]);
    }

    /**
     * @Route("/new", name="language_new")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request The request.
     *
     * @return Response
     */
    public function newAction(Request $request): Response
    {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($language);
                $em->flush();

                $this->addFlash('success', 'New language has been added.');

                return $this->redirectToRoute('language_list');
            }
        }

        return $this->render('app/language/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{code}", name="language_edit")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request  $request  The request.
     * @param Language $language The language.
     *
     * @return Response
     */
    public function editAction(Request $request, Language $language): Response
    {
        $form = $this->createForm(LanguageType::class, $language);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($language);
                $em->flush();

                $this->addFlash('success', 'The language has been edited.');

                return $this->redirectToRoute('language_list');
            }
        }

        return $this->render('app/language/edit.html.twig', [
            'form' => $form->createView(),
            'language' => $language,
        ]);
    }

    /**
     * @Route("/delete/{url}", name="language_delete", methods={"POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Language $language The language.
     *
     * @return Response
     */
    public function deleteAction( Language $language): Response
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($language);
        $em->flush();

        $this->addFlash('success', 'The language has been deleted.');

        return $this->redirectToRoute('language_list');
    }
}
