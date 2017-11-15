<?php

namespace Alejandrito_Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('Alejandrito_Bundle:Default:index.html.twig');
    }
}
