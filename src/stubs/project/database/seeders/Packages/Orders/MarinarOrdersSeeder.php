<?php
namespace Database\Seeders\Packages\Orders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MarinarOrdersSeeder extends Seeder {

    public function run() {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::upsert([
            ['guard_name' => 'admin', 'name' => 'orders.view'],
            ['guard_name' => 'admin', 'name' => 'order.create'],
            ['guard_name' => 'admin', 'name' => 'order.update'],
            ['guard_name' => 'admin', 'name' => 'order.delete'],
        ], ['guard_name','name']);
    }
}
