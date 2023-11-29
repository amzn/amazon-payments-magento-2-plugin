<?php
// phpcs:ignoreFile
// This file throws an error because it is not using db_schema

/**
 * Magento console installer options for Web API functional tests. Are used in functional tests bootstrap.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'language'                     => 'en_US',
    'timezone'                     => 'America/Los_Angeles',
    'currency'                     => 'USD',
    'db-host'                      => 'db',
    'db-name'                      => 'default',
    'db-user'                      => 'root',
    'db-password'                  => 'root',
    'backend-frontname'            => 'admin',
    'base-url'                     => 'https://<magento-url>',
    'use-secure'                   => '0',
    'use-rewrites'                 => '0',
    'admin-lastname'               => 'Test',
    'admin-firstname'              => 'Test',
    'admin-email'                  => 'admin@example.com',
    'admin-user'                   => 'test_admin',
    'admin-password'               => '123123q',
    'admin-use-security-key'       => '0',
    /* PayPal has limitation for order number - 20 characters. 10 digits prefix + 8 digits number is good enough */
    'sales-order-increment-prefix' => time(),
    'session-save'                 => 'db',
    'cleanup-database'             => true,
];
