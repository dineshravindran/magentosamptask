<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace PayPal\Braintree\Gateway\Command;

use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Command\ResultInterface;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Gateway\Validator\PaymentNonceResponseValidator;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Exception\LocalizedException;

class GetPaymentNonceCommand implements CommandInterface
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private PaymentTokenManagementInterface $tokenManagement;

    /**
     * @var BraintreeAdapter
     */
    private BraintreeAdapter $adapter;

    /**
     * @var ArrayResultFactory
     */
    private ArrayResultFactory $resultFactory;

    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var PaymentNonceResponseValidator
     */
    private PaymentNonceResponseValidator $responseValidator;

    /**
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param BraintreeAdapter $adapter
     * @param ArrayResultFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param PaymentNonceResponseValidator $responseValidator
     */
    public function __construct(
        PaymentTokenManagementInterface $tokenManagement,
        BraintreeAdapter $adapter,
        ArrayResultFactory $resultFactory,
        SubjectReader $subjectReader,
        PaymentNonceResponseValidator $responseValidator
    ) {
        $this->tokenManagement = $tokenManagement;
        $this->adapter = $adapter;
        $this->resultFactory = $resultFactory;
        $this->subjectReader = $subjectReader;
        $this->responseValidator = $responseValidator;
    }

    /**
     * @inheritdoc
     *
     * @param array $commandSubject
     * @return ArrayResult|ResultInterface|null
     * @throws LocalizedException
     */
    public function execute(array $commandSubject): ArrayResult|ResultInterface|null
    {
        $publicHash = $this->subjectReader->readPublicHash($commandSubject);
        $customerId = $this->subjectReader->readCustomerId($commandSubject);
        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);

        if (!$paymentToken || !$paymentToken->getIsActive()) {
            throw new LocalizedException(__('No available payment tokens'));
        }

        $data = $this->adapter->createNonce($paymentToken->getGatewayToken());
        $result = $this->responseValidator->validate(['response' => ['object' => $data]]);

        if (!$result->isValid()) {
            throw new LocalizedException(__(implode("\n", $result->getFailsDescription())));
        }

        return $this->resultFactory->create([
            'array' => [
                'paymentMethodNonce' => $data->paymentMethodNonce->nonce,
                'details' => $data->paymentMethodNonce->details
            ]
        ]);
    }
}
