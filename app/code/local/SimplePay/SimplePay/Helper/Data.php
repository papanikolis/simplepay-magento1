<?php

class SimplePay_SimplePay_Helper_Data extends Mage_Core_Helper_Abstract
{
    function getPaymentGatewayUrl()
    {
        return Mage::getUrl('simplepay/payment/gateway', array('_secure' => false));
    }

    public function getConfigurations()
    {
        $paymentMethod = Mage::getModel('simplepay/payment');
        $publicAPIKey = $paymentMethod->getConfigData('live_public_api_key');
        if ($paymentMethod->getConfigData('test_mode')) {
            $publicAPIKey = $paymentMethod->getConfigData('test_public_api_key');
        }

        return array(
            'public_api_key' => $publicAPIKey,
            'description' => $paymentMethod->getConfigData('description'),
            'image_url' => $paymentMethod->getConfigData('image_url')
        );
    }

    public function customerData()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $data['email'] = $customerData->getEmail();

            $customer = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();

            if ($customer) {
                $data['telephone'] = $customer->getTelephone();
                $data['street'] = $customer->getStreet1()  . ' ' . $customer->getStreet2();
                $data['postcode'] = $customer->getPostcode();
                $data['city'] = $customer->getCity();
                $data['country_id'] = $customer->getCountry();
            }

            return $data;
        }
    }
}