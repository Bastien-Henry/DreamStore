<?php
namespace DreamStore\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Order as BaseOrder;

/**
 * @ORM\Table(name="order")
 * @ORM\Entity
 */
class Order extends BaseOrder
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;
}

?>