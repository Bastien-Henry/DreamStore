<?php

namespace DreamStore\SellerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', 'integer', array(
                "label"     => "Price",
                "required"   => true
            ));
    }

    public function getName()
    {
        return 'dreamstore_sellerbundle_pricetype';
    }
}
