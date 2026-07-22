<?php

namespace Database\Seeders;

use App\Modules\IdentityAccess\Domain\Enums\Role;
use App\Modules\IdentityAccess\Domain\Enums\UserStatus;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\Region;
use App\Modules\Organization\Models\Province;
use App\Modules\Organization\Models\District;
use App\Modules\Organization\Models\Store;
use App\Modules\BankingNetwork\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OperationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(
            ['name' => 'Red Principal'],
            [
                'public_id' => (string) Str::uuid(),
                'timezone' => 'America/Lima',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $region = Region::create([
            'organization_id' => $org->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $province = Province::create([
            'organization_id' => $org->id,
            'region_id' => $region->id,
            'name' => 'Lima',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $districtNames = ['Cercado de Lima', 'Miraflores', 'San Isidro'];
        foreach ($districtNames as $name) {
            District::create([
                'organization_id' => $org->id,
                'province_id' => $province->id,
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $districts = District::all();

        Store::create([
            'organization_id' => $org->id,
            'district_id' => $districts->firstWhere('name', 'Miraflores')->id,
            'code' => 'ST-EJEMPLO',
            'name' => 'Tienda de Ejemplo',
            'address' => 'Av. Ejemplo 123, Miraflores',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $banks = [
            ['code' => 'BCP', 'name' => 'Banco de Crédito del Perú'],
            ['code' => 'INTERBANK', 'name' => 'Interbank'],
            ['code' => 'BBVA', 'name' => 'BBVA Perú'],
        ];

        foreach ($banks as $bank) {
            Bank::create([
                'organization_id' => $org->id,
                'code' => $bank['code'],
                'name' => $bank['name'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
