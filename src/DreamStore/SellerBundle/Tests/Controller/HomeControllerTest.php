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
        $this->_em->beginTransaction();
    }

    /**
     * Rollback changes.
     */
    public function tearDown()
    {
        $this->_em->rollback();
    }

    public function testStock()
    {
        $productBefor = $this->_em->getRepository('DreamStoreSellerBundle:Product')->findOneById(1);

        $stockBefor = $productBefor->getStock();
        $table = ['operation' => 'add', 'stock' => 5];
/*
        var_dump($table);
        var_dump($productBefor->getStock());
        die();*/
        $home = new HomeController();
        $home->addNewStock($productBefor, $table);

        /*$productBefor = $this->_em->getRepository('DreamStoreSellerBundle:Product')->findOneById(1);
        $stockAfter = $productBefor->getStock();*/
    }
}
