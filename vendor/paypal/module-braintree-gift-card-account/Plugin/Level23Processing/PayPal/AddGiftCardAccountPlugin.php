<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace PayPal\BraintreeGiftCardAccount\Plugin\Level23Processing\PayPal;

use Braintree\TransactionLineItem;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PayPal\Braintree\Gateway\Config\PayPal\Config as PayPalConfig;
use PayPal\Braintree\Gateway\Data\Order\OrderAdapter;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Request\PayPal\Level23ProcessingDataBuilder;

class AddGiftCardAccountPlugin
{
    /**
     * @var PayPalConfig
     */
    private PayPalConfig $payPalConfig;

    /**
     * @var quoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * Constructor
     *
     * @param PayPalConfig $payPalConfig
     * @param QuoteRepository $quoteRepository
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        PayPalConfig $payPalConfig,
        QuoteRepository $quoteRepository,
        SubjectReader $subjectReader
    ) {
        $this->payPalConfig = $payPalConfig;
        $this->quoteRepository = $quoteRepository;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Add 'Gift Cards' as Line Items for the PayPal transactions
     *
     * @param Level23ProcessingDataBuilder $subject
     * @param array $result
     * @param array $buildSubject
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterBuild(
        Level23ProcessingDataBuilder $subject,
        array $result,
        array $buildSubject
    ): array {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        /** @var OrderAdapter $order */
        $order = $paymentDO->getOrder();

        $ppCartLineItems = $this->payPalConfig->canSendCartLineItemsForPayPal();
        $isBtPayPal = $payment->getMethod() === 'braintree_paypal'
            || $payment->getMethod() === 'braintree_paypal_vault';

        if (isset($result[Level23ProcessingDataBuilder::KEY_LINE_ITEMS])
            && $ppCartLineItems
            && $isBtPayPal
        ) {
            $lineItems = $result[Level23ProcessingDataBuilder::KEY_LINE_ITEMS];

            /** Render quote from order to get gift cards amount */
            $quote = $this->quoteRepository->get($order->getQuoteId());

            /**
             * Adds Gift Cards as credit LineItems for the PayPal
             * transaction if it is greater than 0(Zero) to manage
             * the totals with server-side implementation as there is
             * no any field exist to send that amount to the Braintree.
             */
            if ($quote->getBaseGiftCardsAmountUsed()) {
                $giftCardsAmount = $subject->numberToString(
                    abs((float)$quote->getBaseGiftCardsAmountUsed()),
                    2
                );
                if ($giftCardsAmount > 0) {
                    $giftCardsItems[] = [
                        'name' => 'Gift Cards',
                        'kind' => TransactionLineItem::CREDIT,
                        'quantity' => 1.00,
                        'unitAmount' => $giftCardsAmount,
                        'totalAmount' => $giftCardsAmount
                    ];

                    $lineItems = array_merge($lineItems, $giftCardsItems);
                }
            }

            if (count($lineItems) < 250) {
                $result[Level23ProcessingDataBuilder::KEY_LINE_ITEMS] = $lineItems;
            } else {
                unset($result[Level23ProcessingDataBuilder::KEY_LINE_ITEMS]);
            }
        }

        return $result;
    }
}
