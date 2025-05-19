<?php

namespace Vendor\Customize\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Cart;

class Savings extends Template
{
    protected $customerSession;
    protected $cart;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        Cart $cart,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getSavings(): float
    {
        $quote = $this->cart->getQuote();
        $total = $quote->getGrandTotal();

        if ($this->isCustomerLoggedIn()) {
            return 0.0;
        }

        $registeredTotal = $total * 0.9;
        return round($total - $registeredTotal, 2);
    }
        public function getGuestCartTotal(): float
    {
        return round($this->cart->getQuote()->getGrandTotal(), 2);
    }

    public function getRegisteredCustomerTotal(): float
    {
        return round($this->getGuestCartTotal() * 0.9, 2);
    }

    public function getSavingsAmount(): float
    {
        if ($this->isCustomerLoggedIn()) {
            return 0.0;
        }
        return round($this->getGuestCartTotal() - $this->getRegisteredCustomerTotal(), 2);
    }

}
