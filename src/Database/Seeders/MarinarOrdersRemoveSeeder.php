<?php
    namespace Marinar\Orders\Database\Seeders;

    use App\Models\PaymentMethod;
    use Illuminate\Database\Seeder;
    use Marinar\Orders\MarinarOrders;
    use Spatie\Permission\Models\Permission;

    class MarinarOrdersRemoveSeeder extends Seeder {

        use \Marinar\Marinar\Traits\MarinarSeedersTrait;

        public static function configure() {
            static::$packageName = 'marinar_orders';
            static::$packageDir = MarinarOrders::getPackageMainDir();
        }

        public function run() {
            if(!in_array(env('APP_ENV'), ['dev', 'local'])) return;

            $this->autoRemove();

            $this->refComponents->info("Done!");
        }

        public function clearMe() {
            $this->refComponents->task("Clear DB", function() {
                foreach(Cart::get() as $cart) {
                    $cart->delete();
                }
                Permission::whereIn('name', [
                    'orders.view',
                    'order.create',
                    'order.update',
                    'order.delete',
                ])
                ->where('guard_name', 'admin')
                ->delete();
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                return true;
            });
        }
    }
