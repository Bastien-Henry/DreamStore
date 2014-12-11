<?php

namespace DreamStore\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $products = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findAll();
        $data["products"] = $products;

        //id facebook et ressource owner
        $token = $this->get('security.context')->getToken()->getAccessToken();
        var_dump($this->get('security.context')->getToken()->getResourceOwnerName());
        $json = file_get_contents('https://graph.facebook.com/me?access_token='.$token);
        $decode = json_decode($json);
        $id = $decode->id;
        var_dump($id);

        $response = $this->render('DreamStoreCustomerBundle:Home:index.html.twig', $data);
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function deconnectionAction()
    {
        $this->get('security.context')->setToken(null);
        return $this->redirect($this->generateUrl('dream_store_customer_homepage'));
    }

    public function historicalAction()
    {
        $username = $this->get('security.context')->getToken()->getUser()->getUsername();
        $historicals = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findBy(array("user" => $username, 'status' => 'paye'), array("date" => "desc"));

        $data["historicals"] = $historicals;

        return $this->render('DreamStoreCustomerBundle:Home:historical.html.twig', $data);
    }

    public function cartAction()
    {
        $username = $this->get('security.context')->getToken()->getUser()->getUsername();
        $carts = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findBy(array('user' => $username, 'status' => 'panier'));
        $finalPrice = 0;
        foreach($carts as $cart)
        {
            $finalPrice += $cart->getPrice();
        }
        $data["carts"] = $carts;
        $data["finalPrice"] = $finalPrice;

        return $this->render('DreamStoreCustomerBundle:Home:cart.html.twig', $data);
    }

    public function cartDeleteAction($id)
    {
        $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $username = $this->get('security.context')->getToken()->getUser()->getUsername();
        $cart = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findOneBy(array('user' => $username, 'status' => 'panier', 'product' => $product));

        $em = $this->getDoctrine()->getManager();
        $em->remove($cart);
        $em->flush();
        return $this->redirect($this->generateUrl('dream_store_customer_cart'));
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
