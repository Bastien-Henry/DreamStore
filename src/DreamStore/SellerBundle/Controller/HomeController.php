<?php

namespace DreamStore\SellerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $this->checkAdmin();

        $products = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findAll();
        $historicals = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findBy(array(), array("date" => "desc"));
        $data["products"] = $products;
        $data["historicals"] = $historicals;

        return $this->render('DreamStoreSellerBundle:Home:index.html.twig', $data);
    }

    public function editPriceAction($id)
    {
        $this->checkAdmin();

        $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $request = $this->get("request");
        $form = $this->createForm("dreamstore_sellerbundle_pricetype", $product);
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            if ($form->isValid())
            {
                $this->editPrice($product);
                return $this->redirect($this->generateUrl('dream_store_seller_homepage'));
            }
        }
        $data["id"] = $id;
        $data["form"] = $form->createView();
        $data["route"] = "dream_store_seller_edit_price";

        return $this->render('DreamStoreSellerBundle:Home:price.html.twig', $data);
    }

    public function editPrice($product)
    {
        $em = $this->getDoctrine()->getManager();
        $table = $this->getRequest()->request->get('dreamstore_sellerbundle_pricetype');
        $em->persist($product);
        $em->flush();
    }

    public function addStockAction($id)
    {
        $username = $this->get('security.context')->getToken()->getUser()->getUsername();
        $ressource = $this->get('security.context')->getToken()->GetResourceOwnerName();


        if ($username !== 'stat1k' && $username !== 'GuillaumeFlambard') 
        {
            if ($ressource !== "my_github") 
            {
                return $this->redirect($this->generateUrl('dream_store_customer_homepage'));
            }

            return $this->redirect($this->generateUrl('dream_store_customer_homepage'));
        }
        
        $request = $this->get("request");
        $form = $this->createForm("dreamstore_sellerbundle_stocktype");
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            if ($form->isValid())
            {
                $table = $this->getRequest()->request->get('dreamstore_sellerbundle_stocktype');
                $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
                $productStock = $product->getStock();
                if($productStock-$table['stock'] >= 0)
                {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($this->addNewStock($product, $table));
                    $em->flush();
                }
                return $this->redirect($this->generateUrl('dream_store_seller_homepage'));
            }
        }
        $data["id"] = $id;
        $data["form"] = $form->createView();
        $data["route"] = "dream_store_seller_add_stock";

        return $this->render('DreamStoreSellerBundle:Home:stock.html.twig', $data);
    }

    public function addNewStock($product, $table)
    {
        $productStock = $product->getStock();

        if($table['operation'] == 'add')
            $product->setStock($productStock+$table['stock']);
        else
            $product->setStock($productStock-$table['stock']);

        return $product;

    }

    private function checkAdmin()
    {
        $username = $this->get('security.context')->getToken()->getUser()->getUsername();
        $ressource = $this->get('security.context')->getToken()->GetResourceOwnerName();
        if (($username !== 'stat1k' && $username !== 'GuillaumeFlambard') || $ressource !== "my_github")
            return $this->redirect($this->generateUrl('dream_store_customer_homepage'));

        return;
    }
}
