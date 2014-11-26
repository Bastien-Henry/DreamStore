<?php

namespace DreamStore\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $products = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findAll();
        $data["products"] = $products;

        return $this->render('DreamStoreCustomerBundle:Home:index.html.twig', $data);
    }

    public function showAction($id)
    {
    	$product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $data["product"] = $product;

    	return $this->render('DreamStoreCustomerBundle:Home:product.html.twig', $data);
    }
}
