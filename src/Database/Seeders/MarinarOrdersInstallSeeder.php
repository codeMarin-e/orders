<?php
    namespace Marinar\Orders\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Marinar\Orders\MarinarOrders;

    class MarinarOrdersInstallSeeder extends Seeder {

        use \Marinar\Marinar\Traits\MarinarSeedersTrait;

        public static function configure() {
            static::$packageName = 'marinar_orders';
            static::$packageDir = MarinarOrders::getPackageMainDir();
        }

        public function run() {
            if(!in_array(env('APP_ENV'), ['dev', 'local'])) return;

            $this->autoInstall();

            $this->refComponents->info("Done!");
        }

    }
