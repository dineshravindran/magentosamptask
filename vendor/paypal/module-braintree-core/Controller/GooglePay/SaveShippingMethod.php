<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace PayPal\Braintree\Controller\GooglePay;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use PayPal\Braintree\Model\GooglePay\Config;
use PayPal\Braintree\Model\Paypal\Helper\ShippingMethodUpdater;

class SaveShippingMethod extends AbstractAction implements
    ActionInterface,
    HttpGetActionInterface,
    HttpPostActionInterface
{
    /**
     * @var ShippingMethodUpdater
     */
    private ShippingMethodUpdater $shippingMethodUpdater;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $config
     * @param Session $checkoutSession
     * @param ShippingMethodUpdater $shippingMethodUpdater
     */
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        ShippingMethodUpdater $shippingMethodUpdater
    ) {
        parent::__construct($context, $config, $checkoutSession);

        $this->shippingMethodUpdater = $shippingMethodUpdater;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $isAjaxGPay = $this->getRequest()->getParam('isAjax');
        $quote = $this->checkoutSession->getQuote();

        try {
            $this->validateQuote($quote);

            $this->shippingMethodUpdater->execute(
                $this->getRequest()->getParam('shipping_method'),
                $quote
            );

            if ($isAjaxGPay) {
                /** @var Page $response */
                $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                $layout = $response->addHandle('paypal_express_review_details')->getLayout();
                $response = $layout->getBlock('page.block')->toHtml();
                $this->getResponse()->setBody($response);

                return;
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        $path = $this->_url->getUrl('*/*/review', ['_secure' => true]);

        if ($isAjaxGPay) {
            $this->getResponse()->setBody(sprintf('<script>window.location.href = "%s";</script>', $path));

            return;
        }

        return $this->_redirect($path);
    }
}
