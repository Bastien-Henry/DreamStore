<?php

namespace DreamStore\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BlockController extends Controller
{
    public function indexAction()
    {
        $products = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findAll();
        $data["products"] = $products;

        $response = $this->render('DreamStoreCustomerBundle:Block:index.html.twig', $data);
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function cartAction()
    {

        $response = $this->render('DreamStoreCustomerBundle:Block:cart.html.twig');
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function historicalAction()
    {

        $response = $this->render('DreamStoreCustomerBundle:Block:historical.html.twig');
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function sellerProductAction()
    {

        $response = $this->render('DreamStoreCustomerBundle:Block:sellerProduct.html.twig');
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function sellerHistoricalAction()
    {

        $response = $this->render('DreamStoreCustomerBundle:Block:sellerHistorical.html.twig');
        $response->setSharedMaxAge(600);

        return $response;
    }
}
