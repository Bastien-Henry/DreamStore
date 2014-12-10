<?php

namespace DreamStore\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DreamStore\CustomerBundle\Entity\Historical;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->url = "https://api-3t.sandbox.paypal.com/nvp";

        $this->email = "basthenry-facilitator_api1.gmail.com";
        $this->password = "ZQU6FW74XXM76VLY";
        $this->signature = "AFcWxV21C7fd0v3bYYYRCpSSRl31AzkjVlkpoJ.CmQdWaxNV221FRwQ-";
    }

    public function indexAction($id)
    {
        $table = $this->getRequest()->request->get('dreamstore_customerbundle_paymenttype');
        $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $stock = $product->getStock();


        if($table['place'] == "cart")
        {
            $userName = $this->get('security.context')->getToken()->getUser()->getUsername();
            $cart = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findOneBy(array("user" => $userName, "status" => "panier", "product" => $product));
            if($cart)
            {
                $totalQuantity = $cart->getQuantity()+$table['quantite'];
                $cart->setQuantity($totalQuantity);
                $cart->setPrice($totalQuantity*$product->getPrice());
                $em = $this->getDoctrine()->getManager();
                $em->persist($cart);
                $em->flush();
            }
            else
            {
                $this->historicalAction($product, $table['quantite'], "", "panier");
            }
            return $this->redirect($this->generateUrl('dream_store_customer_homepage'));
        }

        if($stock-$table['quantite'] < 0)
            return $this->redirect($this->generateUrl('dream_store_customer_homepage'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST,1);

        $post_var = array(
                "METHOD" => "SetExpressCheckout",
                "USER" => $this->email,
                "PWD" => $this->password,
                "SIGNATURE" => $this->signature,
                "VERSION" => 78,
                "AMT" => $table['quantite'],
                "returnUrl" => "http://localhost/DreamStore/web/app_dev.php/payment/return",
                "cancelUrl" => "http://localhost/DreamStore/web/app_dev.php/show/".$id
            );

        $post_var['L_PAYMENTREQUEST_0_NAME0']=$product->getName();
        $post_var['L_PAYMENTREQUEST_0_DESC0']=$product->getDescription();
        $post_var['L_PAYMENTREQUEST_0_AMT0']=$product->getPrice();
        $post_var['L_PAYMENTREQUEST_0_QTY0']=$table['quantite'];
        $post_var['PAYMENTREQUEST_0_ITEMAMT']=$product->getPrice() * $table['quantite'];
        $post_var['PAYMENTREQUEST_0_TAXAMT']=$product->getPrice() * 0.2 * $table['quantite'];
        $post_var['PAYMENTREQUEST_0_SHIPPINGAMT']=4.00;
        $post_var['PAYMENTREQUEST_0_HANDLINGAMT']=0.00;
        $post_var['PAYMENTREQUEST_0_QTY'] = $table['quantite'];
        $post_var['PAYMENTREQUEST_0_AMT']= 4.00 + $post_var['PAYMENTREQUEST_0_TAXAMT'] + ($product->getPrice() * $table['quantite']);
        $post_var['PAYMENTREQUEST_0_CURRENCYCODE']="EUR";
        $post_var['ALLOWNOTE']=1;


        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_var));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        if (empty($server_output)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        $response = array();
        parse_str($server_output, $response);
        $this->historicalAction($product, $table['quantite'], $response['TOKEN'], 'en cours');
        $link = $this->getPaypalRedirectUrl($response['TOKEN']);
        return $this->redirect($link);
    }

    public function returnAction()
    {
        $getPaymentResult = $this->getPayment($_GET['token'], $_GET['PayerID']);

        if ($getPaymentResult['PAYERSTATUS'] == "verified") 
        {
            $resultPayment = $this->capturePayment($_GET['PayerID'], $_GET['token']);
            if ($resultPayment['PAYMENTINFO_0_ACK'] == "Success") 
            {
                $historic = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findOneByToken($_GET['token']);
                $historic->setStatus('payé');
                $em = $this->getDoctrine()->getManager();
                $em->persist($historic);
                $em->flush();
                $product = $historic->getProduct();
                $quantity = $historic->getQuantity();

                $data['result'] = true;
                $this->editStockAction($product, $quantity);
                return $this->render('DreamStoreCustomerBundle:Home:returnPayment.html.twig', $data);
            }
            else
            {
                $data['result'] = false;
                return $this->render('DreamStoreCustomerBundle:Home:returnPayment.html.twig', $data);
            }
        }
        else
        {
            echo "Erreur lors de la transaction de Paypal";
        }
    }

    private function getPayment($tokenID, $payerID)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST,1);

        $post_var = array(
                "METHOD" => "GetExpressCheckoutDetails",
                "USER" => $this->email,
                "PWD" => $this->password,
                "SIGNATURE" => $this->signature,
                "VERSION" => 78,
                "TOKEN" => $tokenID,
                "PAYERID" => $payerID
            );

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_var));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        if (empty($server_output)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        $response = array();
        parse_str($server_output, $response);
        return $response;
    }

    function capturePayment($payerID, $tokenID)
    {
        $payment = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findOneByToken($tokenID);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST,1);

        $post_var = array(
                "METHOD" => "DoExpressCheckoutPayment",
                "USER" => $this->email,
                "PWD" => $this->password,
                "SIGNATURE" => $this->signature,
                "VERSION" => 78,
                "TOKEN" => $payment->getToken(),
                "PAYERID" => $payerID,
                "PAYMENTREQUEST_0_PAYMENTACTION" => "SALE",
                "PAYMENTREQUEST_0_CURRENCYCODE" => "EUR"
            );

        // $post_var['PAYMENTREQUEST_0_TAXAMT'] = $payment->getPrice() * 0.2 * $payment->getQuantity();
        $tax = $payment->getPrice() * 0.2 * $payment->getQuantity();

        $post_var['PAYMENTREQUEST_0_AMT'] = 4.00 + $tax + ($payment->getPrice() * $payment->getQuantity());

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_var));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        if (empty($server_output)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);
        $response = array();
        parse_str($server_output, $response);
        return $response;
  }

    private function editStockAction($product, $quantity)
    {
        $stock = $product->getStock();
        $product->setStock($stock - $quantity);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();
        return;
    }

    private function historicalAction($product, $quantity, $token, $status)
    {
        $usr = $this->get('security.context')->getToken()->getUser();
        $userName = $usr->getUsername();

        $historic = new Historical;
        $historic->setProduct($product);
        $historic->setQuantity($quantity);
        $historic->setUser($userName);
        $historic->setToken($token);
        $historic->setStatus($status);
        $historic->setPrice($quantity*$product->getPrice());

        $em = $this->getDoctrine()->getManager();
        $em->persist($historic);
        $em->flush();
        return;
    }

    public function getPaypalRedirectUrl($token)
    {
        return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=".$token;
    }
}
