<?php
	return [
		'install' => [
            'php artisan db:seed --class="\Marinar\Orders\Database\Seeders\MarinarOrdersInstallSeeder"',
		],
		'remove' => [
            'php artisan db:seed --class="\Marinar\Orders\Database\Seeders\MarinarOrdersRemoveSeeder"',
        ]
	];
