<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Gateway\DataResolver\Customer;

use Magento\Vault\Api\Data\PaymentTokenInterface;

interface GetCustomerIdByPaymentTokenInterface
{
    /**
     * Get the Braintree Customer ID, from Braintree directly.
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string|null
     */
    public function execute(PaymentTokenInterface $paymentToken): ?string;
}
