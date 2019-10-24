<?php
/**
 * Add email cc field to customer account area. Transactional emails are also sent to this address.
 * Copyright (C) 2018 Dominic Xigen
 * This file included in Xigen/CC is licensed under OSL 3.0
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Xigen\CC\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Install email_cc customer attribute
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * Constructor
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'email_cc',
            [
                'type' => 'varchar',
                'label' => 'Email CC',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => true,
                'position' => 500,
                'system' => false,
                'backend' => '',
                'user_defined' => true,
                'note' => __("Comma separated"),
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'email_cc')
            ->addData(
                [
                    'used_in_forms' => [
                        'adminhtml_customer',
                        'adminhtml_checkout',
                        'customer_account_create',
                        'customer_account_edit',
                    ],
                ]
            );

        $attribute->save();
    }
}
