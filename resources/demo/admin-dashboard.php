<?php

return [
    'metrics' => [
        'gross_amount' => 'S/ 4,250,192.00',
        'gross_amount_trend' => '+12.5% vs ayer',
        'cash_in' => 'S/ 2,850,000.00',
        'cash_in_ops' => '14,203 ops',
        'cash_out' => 'S/ 1,400,192.00',
        'cash_out_ops' => '8,105 ops',
        'net_movement' => '+S/ 1,449,808.00',
        'total_ops' => '22,308',
        'active_workers' => '412',
        'active_stores' => '84',
        'active_agents' => '156',
        'voided_ops' => '42',
    ],
    'evolution' => [
        'labels' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
        'data' => [120, 250, 400, 450, 380, 200, 350, 480, 520, 300],
    ],
    'bank_distribution' => [
        ['bank' => 'BCP', 'percentage' => 45],
        ['bank' => 'Interbank', 'percentage' => 30],
        ['bank' => 'BBVA', 'percentage' => 15],
        ['bank' => 'Otros', 'percentage' => 10],
    ],
    'flow_by_region' => [
        'labels' => ['Norte', 'Central', 'Sur', 'Este'],
        'cash_in' => [450, 800, 300, 200],
        'cash_out' => [350, 600, 250, 180],
    ],
    'top_stores' => [
        ['rank' => 1, 'name' => 'Plaza Central', 'code' => 'LIMA-01', 'volume' => 'S/ 842,000'],
        ['rank' => 2, 'name' => 'Mall Sur', 'code' => 'LIMA-04', 'volume' => 'S/ 610,000'],
        ['rank' => 3, 'name' => 'Arequipa Centro', 'code' => 'ARQ-01', 'volume' => 'S/ 455,000'],
        ['rank' => 4, 'name' => 'Trujillo Norte', 'code' => 'TRU-02', 'volume' => 'S/ 320,000'],
        ['rank' => 5, 'name' => 'San Isidro Empresarial', 'code' => 'LIMA-12', 'volume' => 'S/ 298,000'],
    ],
    'top_workers' => [
        ['initials' => 'JC', 'name' => 'Jorge Campos', 'id' => '8832', 'ops' => '412 ops', 'status' => 'active'],
        ['initials' => 'MR', 'name' => 'María Rojas', 'id' => '9102', 'ops' => '389 ops', 'status' => 'active'],
        ['initials' => 'AL', 'name' => 'Ana López', 'id' => '4421', 'ops' => '350 ops', 'status' => 'offline'],
        ['initials' => 'LP', 'name' => 'Luis Pérez', 'id' => '7731', 'ops' => '315 ops', 'status' => 'active'],
        ['initials' => 'CM', 'name' => 'Carlos Mendoza', 'id' => '2290', 'ops' => '299 ops', 'status' => 'active'],
    ],
];
