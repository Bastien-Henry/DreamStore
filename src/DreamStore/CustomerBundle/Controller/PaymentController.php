<?php

namespace DreamStore\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
                "returnUrl" => "http://localhost/DreamStore/web/app_dev.php",
                "cancelUrl" => "http://localhost/DreamStore/web/app_dev.php"
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
        $link = $this->getPaypalRedirectUrl($response['TOKEN']);
        return $this->redirect($link);
    }

        public function getPaypalRedirectUrl($token)
    {
        return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=".$token;
    }
}
