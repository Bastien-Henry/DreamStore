<?php

namespace DreamStore\SellerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DreamStore\SellerBundle\Controller\HomeController;

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

    public function testAddStock()
    {
        $productBefore = $this->_em->getRepository('DreamStoreSellerBundle:Product')->findOneById(1);

        $stockBefor = $productBefore->getStock();
        $table = ['operation' => 'add', 'stock' => 5];

        $home = new HomeController();
        $productAfter = $home->addNewStock($productBefore, $table);

        $this->assertEquals($stockBefor+5, $productAfter->getStock());
    }

    public function testRemoveStock()
    {
        $productBefore = $this->_em->getRepository('DreamStoreSellerBundle:Product')->findOneById(1);

        $stockBefor = $productBefore->getStock();
        $table = ['operation' => 'remove', 'stock' => 5];

        $home = new HomeController();
        $productAfter = $home->addNewStock($productBefore, $table);

        $this->assertEquals($stockBefor-5, $productAfter->getStock());
    }
}
