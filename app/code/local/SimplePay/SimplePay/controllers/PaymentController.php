<?php

class SimplePay_SimplePay_PaymentController extends Mage_Core_Controller_Front_Action
{
    //  Take place at the payment gateway end to process the secure payment
    public function gatewayAction()
    {
        if ($this->getRequest()->get("orderId")) {
            $arr_querystring = array(
                'flag' => 1,
                'orderId' => $this->getRequest()->get("orderId"),
                'token' => $this->getRequest()->get("token")
            );

            Mage_Core_Controller_Varien_Action::_redirect('simplepay/payment/response', array('_secure' => false, '_query' => $arr_querystring));
        }
    }

    public function redirectAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','simplepay',array('template' => 'simplepay/redirect.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function isPaymentValid()
    {
        $paymentMethod = Mage::getModel('simplepay/payment');
        $privateAPIKey = $paymentMethod->getConfigData('live_private_api_key');
        if ($paymentMethod->getConfigData('test_mode')) {
            $privateAPIKey = $paymentMethod->getConfigData('test_private_api_key');
        }

        $data = array(
            'token' => $this->getRequest()->get("token")
        );
        $dataString = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://checkout.simplepay.ng/v1/payments/verify/');
        curl_setopt($ch, CURLOPT_USERPWD, $privateAPIKey . ':');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString)
        ));

        $curlResponse = curl_exec($ch);
        $curlResponse = preg_split("/\r\n\r\n/", $curlResponse);
        $responseContent = $curlResponse[1];
        $json_response = json_decode(chop($responseContent), TRUE);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // even is http status code is 200 we still need to check transaction had issues or not
        if ($responseCode == '200' && $json_response['response_code'] == '20000') {
            return true;
        }
        return false;
    }

    // Handle response action
    public function responseAction()
    {
        if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId") && $this->isPaymentValid()) {
            $orderId = $this->getRequest()->get("orderId");
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Payment Success.');
            $order->save();
            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure' => false));

        } else {
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure' => false));
        }
    }
}