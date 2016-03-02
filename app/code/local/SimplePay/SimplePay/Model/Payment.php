<?php

class SimplePay_SimplePay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'simplepay';

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('simplepay/payment/redirect', array('_secure' => false));
    }
}