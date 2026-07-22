<?php

namespace Database\Seeders;

use App\Modules\Operations\Models\OperationType;
use App\Modules\Organization\Models\Organization;
use App\Modules\BankingNetwork\Models\Bank;
use Illuminate\Database\Seeder;

class OperationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Organization::all();

        if ($orgs->isEmpty()) {
            return;
        }

        $banks = Bank::all();

        $generalTypes = [
            ['name' => 'Depósito', 'cash_direction' => 'ENTRADA', 'description' => 'Depósito en efectivo o cheque'],
            ['name' => 'Retiro', 'cash_direction' => 'SALIDA', 'description' => 'Retiro en efectivo'],
            ['name' => 'Consulta', 'cash_direction' => 'NEUTRA', 'description' => 'Consulta de saldo o movimientos'],
            ['name' => 'Pago de servicios', 'cash_direction' => 'SALIDA', 'description' => 'Pago de servicios públicos o privados'],
            ['name' => 'Transferencia', 'cash_direction' => 'NEUTRA', 'description' => 'Transferencia entre cuentas'],
        ];

        foreach ($orgs as $org) {
            foreach ($generalTypes as $type) {
                OperationType::firstOrCreate(
                    [
                        'organization_id' => $org->id,
                        'bank_id' => null,
                        'name' => $type['name'],
                    ],
                    [
                        'description' => $type['description'],
                        'cash_direction' => $type['cash_direction'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            foreach ($banks as $bank) {
                foreach ($generalTypes as $type) {
                    OperationType::firstOrCreate(
                        [
                            'organization_id' => $org->id,
                            'bank_id' => $bank->id,
                            'name' => $type['name'],
                        ],
                        [
                            'description' => $type['description'] . ' - ' . $bank->name,
                            'cash_direction' => $type['cash_direction'],
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
