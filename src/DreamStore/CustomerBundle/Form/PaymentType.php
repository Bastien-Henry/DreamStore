<?php

namespace DreamStore\CustomerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantite', 'integer', array(
                "label"     => "Quantité",
                "required"   => true
            ));
    }

    public function getName()
    {
        return 'dreamstore_customerbundle_paymenttype';
    }
}