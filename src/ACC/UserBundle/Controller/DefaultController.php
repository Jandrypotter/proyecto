<?php

namespace ACC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ACCUserBundle:Default:index.html.twig');
    }
}
