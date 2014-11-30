<?php

namespace DreamStore\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $products = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findAll();
        $data["products"] = $products;

        //Name of the user
        //$user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('DreamStoreCustomerBundle:Home:index.html.twig', $data);
    }

    public function showAction($id)
    {
    	$product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $data["product"] = $product;

    	return $this->render('DreamStoreCustomerBundle:Home:product.html.twig', $data);
    }
}
