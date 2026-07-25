<?php

namespace Database\Seeders;

use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Seeder;

class OperationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Organization::all();

        if ($orgs->isEmpty()) {
            return;
        }

        $types = [
            ['name' => 'Depósito', 'cash_multiplier' => 1, 'digital_multiplier' => 0, 'description' => 'Depósito en efectivo', 'sort_order' => 1],
            ['name' => 'Retiro', 'cash_multiplier' => -1, 'digital_multiplier' => 0, 'description' => 'Retiro en efectivo', 'sort_order' => 2],
            ['name' => 'Transferencia', 'cash_multiplier' => 1, 'digital_multiplier' => -1, 'description' => 'Transferencia entre cuentas', 'sort_order' => 3],
            ['name' => 'Pago de servicios', 'cash_multiplier' => -1, 'digital_multiplier' => 0, 'description' => 'Pago de servicios públicos o privados', 'sort_order' => 4],
            ['name' => 'Recarga', 'cash_multiplier' => -1, 'digital_multiplier' => 0, 'description' => 'Recarga de celular', 'sort_order' => 5],
            ['name' => 'Cobro', 'cash_multiplier' => 1, 'digital_multiplier' => 0, 'description' => 'Cobro de recibos', 'sort_order' => 6],
            ['name' => 'Envío', 'cash_multiplier' => -1, 'digital_multiplier' => 0, 'description' => 'Envío de dinero', 'sort_order' => 7],
            ['name' => 'Otro', 'cash_multiplier' => 0, 'digital_multiplier' => 0, 'description' => 'Otra operación', 'sort_order' => 8],
        ];

        foreach ($orgs as $org) {
            foreach ($types as $type) {
                OperationType::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'name' => $type['name'],
                    ],
                    [
                        'description' => $type['description'],
                        'cash_multiplier' => $type['cash_multiplier'],
                        'digital_multiplier' => $type['digital_multiplier'],
                        'sort_order' => $type['sort_order'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
