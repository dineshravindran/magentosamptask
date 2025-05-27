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
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getSavingsAmount()
    {
        $items = $this->cart->getQuote()->getAllVisibleItems();
        $totalGuest = 0;
        $totalRegistered = 0;

        foreach ($items as $item) {
            $qty = $item->getQty();
            $priceGuest = $item->getPrice(); 
            $priceRegistered = $this->getCustomerPrice($item); 

            $totalGuest += $priceGuest * $qty;
            $totalRegistered += $priceRegistered * $qty;
        }

        return max(0, $totalGuest - $totalRegistered);
    }

    protected function getCustomerPrice($item)
    {
        return $item->getPrice() * 0.9;
    }

    // public function getSavings(): float
    // {
    //     $quote = $this->cart->getQuote();
    //     $total = $quote->getGrandTotal();

    //     if ($this->isCustomerLoggedIn()) {
    //         return 0.0;
    //     }

    //     $registeredTotal = $total * 0.9;
    //     return round($total - $registeredTotal, 2);
    // }
    //     public function getGuestCartTotal(): float
    // {
    //     return round($this->cart->getQuote()->getGrandTotal(), 2);
    // }

    // public function getRegisteredCustomerTotal(): float
    // {
    //     return round($this->getGuestCartTotal() * 0.9, 2);
    // }

    // public function getSavingsAmount(): float
    // {
    //     if ($this->isCustomerLoggedIn()) {
    //         return 0.0;
    //     }
    //     return round($this->getGuestCartTotal() - $this->getRegisteredCustomerTotal(), 2);
    // }

}
