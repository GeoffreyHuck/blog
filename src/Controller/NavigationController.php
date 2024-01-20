<?php
namespace App\Controller;

use App\Entity\Language;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class NavigationController extends AbstractController
{
    public function renderNav(): Response
    {
        $languages = $this->getDoctrine()->getRepository(Language::class)->findAll();

        return $this->render('app/navigation/renderNav.html.twig', [
            'languages' => $languages,
        ]);
    }
}
