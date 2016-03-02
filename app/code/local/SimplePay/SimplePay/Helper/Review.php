<?php

class SimplePay_SimplePay_Helper_Review extends Mage_Core_Helper_Abstract
{
    /**
     * Get template for button in order review page if SimplePay method was selected
     *
     * @param string $name template name
     * @param string $block buttons block name
     * @return string
     */
    public function getReviewButtonTemplate($name, $block)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote) {
            $selectedPaymentMethod = $quote->getPayment()->getMethodInstance()->getCode();
            if ($selectedPaymentMethod == Mage::getModel('simplepay/payment')->getCode()) {
                return $name;
            }
        }

        if ($blockObject = Mage::getSingleton('core/layout')->getBlock($block)) {
            return $blockObject->getTemplate();
        }

        return '';
    }
}
