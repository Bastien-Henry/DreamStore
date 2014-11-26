<?php

namespace DreamStore\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Payum\Core\Request\GetHumanStatus;

class PaymentController extends Controller 
{
    public function prepareAction()
    {
        $paymentName = 'dreamstore_paypal_express';

        $storage = $this->get('payum')->getStorage('DreamStore\PaymentBundle\Entity\PaymentDetails');

        /** @var \DreamStore\PaymentBundle\Entity\PaymentDetails $details */
        $details = $storage->createModel();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = 'USD';
        $details['PAYMENTREQUEST_0_AMT'] = 1.23;
        $storage->updateModel($details);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $details,
            'dream_store_payment_done' // the route to redirect after capture;
        );

        $details['INVNUM'] = $details->getId();
        $details['RETURNURL'] = $captureToken->getTargetUrl();
        $details['CANCELURL'] = $captureToken->getTargetUrl();
        $storage->updateModel($details);

        return $this->redirect($captureToken->getTargetUrl());
    }

    public function captureDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $payment = $this->get('payum')->getPayment($token->getPaymentName());

        $payment->execute($status = new GetHumanStatus($token));
        if ($status->isCaptured()) {
            $this->getUser()->addCredits(100);
            $this->get('session')->getFlashBag()->set(
                'notice',
                'Payment success. Credits were added'
            );
        } else if ($status->isPending()) {
            $this->get('session')->getFlashBag()->set(
                'notice',
                'Payment is still pending. Credits were not added'
            );
        } else {
            $this->get('session')->getFlashBag()->set('error', 'Payment failed');
        }

        return $this->redirect('homepage');
    }
}