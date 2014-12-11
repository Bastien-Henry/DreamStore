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

        if ($table !== null) 
        {

            $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
            $stock = $product->getStock();

            if($stock - $table['quantite'] < 0 || is_int($table['quantite']) == false || $table['quantite'] <= 0)
            {
                $form = $this->createForm("dreamstore_customerbundle_paymenttype");


                $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
                $data["product"] = $product;

                $data['form'] = $form->createView();
                $data['route'] = "dream_store_customer_payment_index";
                $data['id'] = $id;
                $data['error'] = "Erreur lors de la saisie du stock !";
                return $this->render('DreamStoreCustomerBundle:Home:product.html.twig', $data); 
            }
        }

        if($table['place'] == "cart")
        {
            $username = $this->checkUser();
            $cart = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findOneBy(array("user" => $username, "status" => "panier", "product" => $product));
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

        
            

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST,1);

        if ($table !== null) 
        {
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
        }
        else
        {
            $username = $this->checkUser();

            $carts = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findBy(array('user' => $username, 'status' => 'panier'));

            if (count($carts) > 1) 
            {
                $quantity = $carts[1]->getQuantity() + $carts[0]->getQuantity();
            }
            else
            {
                $quantity = $carts[0]->getQuantity();
            }

            

            $post_var = array(
                "METHOD" => "SetExpressCheckout",
                "USER" => $this->email,
                "PWD" => $this->password,
                "SIGNATURE" => $this->signature,
                "VERSION" => 78,
                "AMT" => $quantity,
                "returnUrl" => "http://localhost/DreamStore/web/app_dev.php/payment/return",
                "cancelUrl" => "http://localhost/DreamStore/web/app_dev.php/show/".$id
            );
            //first item
            $post_var['L_PAYMENTREQUEST_0_NAME0']=$carts[0]->getProduct()->getName();
            $post_var['L_PAYMENTREQUEST_0_DESC0']=$carts[0]->getProduct()->getDescription();
            $post_var['L_PAYMENTREQUEST_0_AMT0']=$carts[0]->getProduct()->getPrice();
            $post_var['L_PAYMENTREQUEST_0_QTY0']=$carts[0]->getQuantity();

            if (count($carts) > 1) 
            {
                //second item
                $post_var['L_PAYMENTREQUEST_0_NAME1']=$carts[1]->getProduct()->getName();
                $post_var['L_PAYMENTREQUEST_0_DESC1']=$carts[1]->getProduct()->getDescription();
                $post_var['L_PAYMENTREQUEST_0_AMT1']=$carts[1]->getProduct()->getPrice();
                $post_var['L_PAYMENTREQUEST_0_QTY1']=$carts[1]->getQuantity();
            }
            

            //le total
            if (count($carts) > 1) 
            {
                $totalAMTItem = ($carts[0]->getProduct()->getPrice() * $carts[0]->getQuantity()) + ($carts[1]->getProduct()->getPrice() * $carts[1]->getQuantity());
            }
            else
            {
                $totalAMTItem = ($carts[0]->getProduct()->getPrice() * $carts[0]->getQuantity());
            }
            
            $post_var['PAYMENTREQUEST_0_ITEMAMT']=$totalAMTItem;

            $post_var['PAYMENTREQUEST_0_TAXAMT']=$totalAMTItem * 0.2;
            $post_var['PAYMENTREQUEST_0_SHIPPINGAMT']=4.00;
            $post_var['PAYMENTREQUEST_0_HANDLINGAMT']=0.00;
            if (count($carts) > 1) 
            {
                $post_var['PAYMENTREQUEST_0_QTY'] = $carts[0]->getQuantity() + $carts[1]->getQuantity();
            }
            else
            {
                $post_var['PAYMENTREQUEST_0_QTY'] = $carts[0]->getQuantity();
            }
            
            $post_var['PAYMENTREQUEST_0_AMT']= 4.00 + $post_var['PAYMENTREQUEST_0_TAXAMT'] + ($totalAMTItem);
            $post_var['PAYMENTREQUEST_0_CURRENCYCODE']="EUR";

            $post_var['ALLOWNOTE']=1;
        }

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
        if ($table !== null) 
        {
            $this->historicalAction($product, $table['quantite'], $response['TOKEN'], 'en cours');
        }
        else
        {
            foreach ($carts as $historic) 
            {
                $historic->setToken($response['TOKEN']);
                $historic->setStatus('en cours');
                $em = $this->getDoctrine()->getManager();
                $em->persist($historic);
                $em->flush();

            }
            
        }
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
                $historic = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findByToken($_GET['token']);
                foreach ($historic as $one) 
                {
                    $one->setStatus('paye');
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($one);
                    $em->flush();
                    $product = $one->getProduct();
                    $quantity = $one->getQuantity();
                    $data['result'] = true;
                    $this->editStockAction($product, $quantity);
                }
                
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
        $payment = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findBy(array('token' => $tokenID, 'status' => 'en cours'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST,1);

        $post_var = array(
                "METHOD" => "DoExpressCheckoutPayment",
                "USER" => $this->email,
                "PWD" => $this->password,
                "SIGNATURE" => $this->signature,
                "VERSION" => 78,
                "TOKEN" => $tokenID,
                "PAYERID" => $payerID,
                "PAYMENTREQUEST_0_PAYMENTACTION" => "SALE",
                "PAYMENTREQUEST_0_CURRENCYCODE" => "EUR"
            );

        $totalAMT = 0;
        foreach ($payment as $arrayPayment) 
        {
            $totalAMT += $arrayPayment->getPrice();
        }
        $taxFinal = $totalAMT * 0.2;

        $post_var['PAYMENTREQUEST_0_AMT'] = 4.00 + $taxFinal + $totalAMT;

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
        $editStock = $this->editStock($product, $quantity);
        $em = $this->getDoctrine()->getManager();
        $em->persist($editStock);
        $em->flush();
        return;
    }

    public function editStock($product, $quantity)
    {
        $stock = $product->getStock();
        $product->setStock($stock - $quantity);
        return $product;
    }

    private function historicalAction($product, $quantity, $token, $status)
    {
        $usr = $this->get('security.context')->getToken()->getUser();
        $username = $this->checkUser();

        $historical = $this->historical($product, $quantity, $username, $token, $status);
        $em = $this->getDoctrine()->getManager();
        $em->persist($historical);
        $em->flush();
    }

    public function historical($product, $quantity, $username, $token, $status)
    {
        $historic = new Historical;
        $historic->setProduct($product);
        $historic->setQuantity($quantity);
        $historic->setUser($username);
        $historic->setToken($token);
        $historic->setStatus($status);
        $historic->setPrice($quantity*$product->getPrice());

        return $historic;
    }

    public function getPaypalRedirectUrl($token)
    {
        return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=".$token;
    }

    public function checkUser()
    {
        if($this->get('security.context')->getToken()->getResourceOwnerName() == 'facebook')
        {
            $token = $this->get('security.context')->getToken()->getAccessToken();
            $json = file_get_contents('https://graph.facebook.com/me?access_token='.$token);
            $decode = json_decode($json);
            $username = $decode->id;
        }
        else
        {
            $username = $this->get('security.context')->getToken()->getUser()->getUsername();
        }

        return $username;
    }
}
