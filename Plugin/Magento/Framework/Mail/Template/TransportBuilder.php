<?php
/**
 * Add email cc field to customer account area. Transactional emails are also sent to this address.
 * Copyright (C) 2018 Dominic Xigen
 *
 * This file included in Xigen/CC is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Xigen\CC\Plugin\Magento\Framework\Mail\Template;

/**
 * Plugin to add customer email cc
 */
class TransportBuilder
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    public function beforeGetTransport(
        \Magento\Framework\Mail\Template\TransportBuilder $subject
    ) {
        try {

            $ccEmailAddresses = $this->getEmailCopyTo();
            if (!empty($ccEmailAddresses)) {
                foreach ($ccEmailAddresses as $ccEmailAddress) {
                    $subject->addCc(trim($ccEmailAddress));
                    $this->logger->debug((string) __('Added customer CC: %1', trim($ccEmailAddress)));
                }
            }

        } catch (\Exception $e) {
            $this->logger->error((string) __('Failure to add customer CC: %1', $e->getMessage()));
        }
        return [];
    }

    /**
     * Get customer id from session
     */
    public function getCustomerIdFromSession()
    {
        if ($customer = $this->customerSession->getCustomer()) {
            return $customer->getId();
        }
        return null;
    }

    /**
     * Return email copy_to list
     * @return array|bool
     */
    public function getEmailCopyTo()
    {

        $customerId = $this->getCustomerIdFromSession();
        if (!$customerId) {
            return false;
        }

        // $this->logger->debug((string) __('Customer Id: %1', $customerId));

        $customer = $this->getCustomerById($customerId);
        if (!$customer) {
            return false;
        }

        $emailCc = $customer->getCustomAttribute('email_cc');
        $customerEmailCC = $emailCc ? $emailCc->getValue() : null;

        // $this->logger->debug((string) __('Customer cc: %1', $customerEmailCC));

        if (!empty($customerEmailCC)) {
            return explode(',', trim($customerEmailCC));
        }

        return false;
    }

    /**
     * Get customer by Id.
     * @param int $customerId
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function getCustomerById($customerId)
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
