<?php

namespace DreamStore\SellerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        $products = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findAll();
        $historicals = $this->getDoctrine()->getRepository('DreamStoreCustomerBundle:Historical')->findAll();
        $data["products"] = $products;
        $data["historicals"] = $historicals;

        return $this->render('DreamStoreSellerBundle:Home:index.html.twig', $data);
    }

    public function editPriceAction($id)
    {
        $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $request = $this->get("request");
        $form = $this->createForm("dreamstore_sellerbundle_pricetype", $product);
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $table = $this->getRequest()->request->get('dreamstore_sellerbundle_pricetype');
                $em->persist($product);
                $em->flush();
                return $this->redirect($this->generateUrl('dream_store_seller_homepage'));
            }
        }
        $data["id"] = $id;
        $data["form"] = $form->createView();
        $data["route"] = "dream_store_seller_edit_price";

        return $this->render('DreamStoreSellerBundle:Home:price.html.twig', $data);
    }

    public function addStockAction($id)
    {
        $product = $this->getDoctrine()->getRepository('DreamStoreSellerBundle:Product')->findOneById($id);
        $request = $this->get("request");
        $form = $this->createForm("dreamstore_sellerbundle_stocktype");
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $table = $this->getRequest()->request->get('dreamstore_sellerbundle_stocktype');
                $ProductStock = $product->getStock();
                if($table['operation'] == 'add')
                {
                    $product->setStock($ProductStock+$table['stock']);
                }
                else
                {
                    $product->setStock($ProductStock-$table['stock']);
                }
                $em->persist($product);
                $em->flush();
                return $this->redirect($this->generateUrl('dream_store_seller_homepage'));
            }
        }

        $data["id"] = $id;
        $data["form"] = $form->createView();
        $data["route"] = "dream_store_seller_add_stock";

        return $this->render('DreamStoreSellerBundle:Home:stock.html.twig', $data);
    }
}
