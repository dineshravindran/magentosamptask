<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Plugin;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;

class ProductDetailsBlockPlugin
{
    /**
     * @var \PayPal\Braintree\Block\Credit\Calculator\Listing\Product
     */
    protected \PayPal\Braintree\Block\Credit\Calculator\Listing\Product $listingBlock;

    /**
     * ProductDetailsBlockPlugin constructor
     *
     * @param \PayPal\Braintree\Block\Credit\Calculator\Listing\Product $listingBlock
     */
    public function __construct(
        \PayPal\Braintree\Block\Credit\Calculator\Listing\Product $listingBlock
    ) {
        $this->listingBlock = $listingBlock;
    }

    /**
     * Get product details html
     *
     * @param ListProduct $subject
     * @param callable $proceed
     * @param Product $product
     * @return string
     */
    public function aroundGetProductDetailsHtml(
        ListProduct $subject,
        callable $proceed,
        Product $product
    ): string {
        $result = $proceed($product);

        if ($product) {
            $this->listingBlock->setProduct($product);
            $result .= $this->listingBlock->toHtml();
        }

        return $result;
    }
}
