<?php

namespace DreamStore\CustomerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DreamStore\CustomerBundle\Controller\PaymentController;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $_em;

    protected function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->_em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testEditStock()
    {
        $productBefore = $this->_em->getRepository('DreamStoreSellerBundle:Product')->findOneById(1);

        $stockBefore = $productBefore->getStock();
        $quantity = 50;

        $payment = new PaymentController();
        $productAfter = $payment->editStock($productBefore, $quantity);

        $this->assertEquals($stockBefore-50, $productAfter->getStock());
    }

    public function testHistorical()
    {
        $product = $this->_em->getRepository('DreamStoreSellerBundle:Product')->findOneById(1);
        $quantity = 50;
        $userName = 'toto';
        $token = 'z49er8c4z9ec';
        $status = 'en cours';

        $payment = new PaymentController();
        $historical = $payment->historical($product, $quantity, $userName, $token, $status);

        $this->assertInstanceOf('DreamStore\CustomerBundle\Entity\Historical', $historical);
    }
}
