<?php
return [
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'resources', 'views', 'components', 'admin', 'box_sidebar.blade.php']) => [
        "{{--  @HOOK_ADMIN_SIDEBAR  --}}" => "\t<x-admin.sidebar.orders_option />\n",
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'config', 'marinar.php']) => [
        "// @HOOK_MARINAR_CONFIG_ADDONS" => "\t\t\\Marinar\\Orders\\MarinarOrders::class, \n"
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'app', 'Console', 'Commands', 'GarbageCollector.php']) => [
        "// @HOOK_CLEANING" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'HOOK_CLEANING.php']),
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'app', 'Models', 'ProductSize.php']) => [
        "// @HOOK_TRAITS" => "\t\t use \\App\\Traits\\ProductSizeOrdersTrait; \n"
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'app', 'Models', 'User.php']) => [
        "// @HOOK_TRAITS" => "\t\t use \\App\\Traits\\UserOrdersTrait; \n"
    ],
];
