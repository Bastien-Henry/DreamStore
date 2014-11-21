<?php

namespace DreamStore\SellerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('operation', 'choice', array(
                "choices" => array(
                    "add" => "ajouter",
                    "remove" => "enlever"
                ),
                "label"     => "Opération",
                "required"   => true
            ))
            ->add('stock', 'integer', array(
                "label"     => "Stock",
                "required"   => true
            ));
    }
/*
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DreamStore\SellerBundle\Entity\Product'
        ));
    }
*/
    public function getName()
    {
        return 'dreamstore_sellerbundle_stocktype';
    }
}
