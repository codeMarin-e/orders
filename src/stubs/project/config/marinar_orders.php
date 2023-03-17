<?php
return [
    /**
     * Behavior when package is installed or update
     * true - normal
     * false - do not do anything
     */
    'install_behavior' => env('MARINAR_ORDERS_INSTALL_BEHAVIOR', env('MARINAR_INSTALL_BEHAVIOR', true)),

    /**
     * Behavior when package is removed from composer
     * true - delete all
     * false - delete all, but not changed stubs files
     * 1 - delete all, but keep the stub files and injection
     * 2 - keep everything
     */
    'delete_behavior' => env('MARINAR_ORDERS_DELETE_BEHAVIOR', env('MARINAR_DELETE_BEHAVIOR', false)),

    /**
     * File stubs that return arrays that are configurable,
     * If path is directory - its files and sub directories
     */
    'values_stubs' => [
        __DIR__,
        dirname(__DIR__).DIRECTORY_SEPARATOR.'lang'
    ],

    /**
     * Exclude stubs to be updated
     * If path is directory - exclude all its files
     * If path is file - only it
     */
    'exclude_stubs' => [
        // @HOOK_CONFIG_EXCLUDE_STUBS
    ],

    /**
     * Exclude addon injections
     * file_path => [ '@hook1', '@hook2' ]
     * file_path => * - all from this file
     */
    'exclude_injects' => [
        // @HOOK_CONFIG_EXCLUDE_INJECTS
    ],

    /**
     * Addons hooked to the package
     */
    'addons' => [
        // @HOOK_ORDERS_CONFIGS_ADDONS
    ],
    // @HOOK_ORDERS_CONFIGS

    /**
     * Reprice current cart at every change
     */
    'reprice_on_change' => true,

    /**
     * When adding cart product to use/or not discount on the cart product owner price
     */
    'add_owner_real_price_with_discount' => true,

    /**
     * When set delivery to use/or not discount on the delivery tax
     */
    'set_delivery_tax_with_discount' => true,

    /**
     * When set payment to use/or not discount on the payment tax
     */
    'set_payment_tax_with_discount' => true,

    /**
     * How old not confirmed processd orders to be cleaned
     * It's from Carbon sub method
     */
    'clean_older_than_processing_from' => '1 day',
];
