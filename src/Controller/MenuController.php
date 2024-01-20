<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function renderMenu(): Response
    {
        return $this->render('app/menu/renderMenu.html.twig');
    }
}
