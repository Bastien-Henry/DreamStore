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
        $form = $this->createForm("dreamstore_customerbundle_paymenttype");

    	$product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $data["product"] = $product;

        $data['form'] = $form->createView();
        $data['route'] = "dream_store_customer_payment_index";
        $data['id'] = $id;


    	return $this->render('DreamStoreCustomerBundle:Home:product.html.twig', $data);
    }
}
