<?php
    namespace Marinar\Orders;

    use Marinar\Orders\Database\Seeders\MarinarOrdersInstallSeeder;

    class MarinarOrders {

        public static function getPackageMainDir() {
            return __DIR__;
        }

        public static function injects() {
            return MarinarOrdersInstallSeeder::class;
        }
    }
