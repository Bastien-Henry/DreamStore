<?php

namespace DreamStore\SellerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DreamStoreSellerBundle:Default:index.html.twig', array('name' => $name));
    }
}
