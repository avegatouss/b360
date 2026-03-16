<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;

class Eshop360DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            Eshop360PermissionsSeeder::class,
        ]);
    }
}
