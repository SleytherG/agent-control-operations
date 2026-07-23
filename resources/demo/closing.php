<?php

return [
    'active' => [
        'id' => 1,
        'status' => 'ACTIVO',
        'status_badge' => 'blue',
        'date' => '24 Oct 2023',
        'store' => 'Plaza Central',
        'bank' => 'Banco de la Nacion',
        'agent' => 'ID: 00124 (M. Gomez)',
        'metrics' => [
            'total_ops' => 142,
            'gross_amount' => 'S/ 45,230.00',
            'total_entradas' => 'S/ 32,100.50',
            'total_salidas' => 'S/ 13,129.50',
            'net_movement' => '+S/ 18,971.00',
        ],
        'pending_confirm' => 1,
        'by_type' => [
            ['type' => 'Depositos', 'count' => 85, 'entradas' => 'S/ 28,500.00', 'salidas' => '-'],
            ['type' => 'Retiros', 'count' => 42, 'entradas' => '-', 'salidas' => 'S/ 12,400.00'],
            ['type' => 'Pago de Servicios', 'count' => 15, 'entradas' => 'S/ 3,600.50', 'salidas' => '-'],
        ],
        'by_worker' => [
            ['name' => 'Carlos Garcia', 'initials' => 'CG', 'ops' => 68, 'amount' => 'S/ 18,450.00'],
            ['name' => 'Laura Martinez', 'initials' => 'LM', 'ops' => 74, 'amount' => 'S/ 26,780.00'],
        ],
        'status_breakdown' => [
            ['label' => 'Activas', 'count' => 138, 'color' => 'active'],
            ['label' => 'Anuladas', 'count' => 2, 'color' => 'annulled'],
            ['label' => 'Pendientes', 'count' => 1, 'color' => 'pending'],
            ['label' => 'Direcciones no conf.', 'count' => 1, 'color' => 'error'],
        ],
        'participants' => [
            ['name' => 'Carlos Garcia', 'role' => 'CAJERO P1'],
            ['name' => 'Laura Martinez', 'role' => 'SUPERVISOR TURNO'],
        ],
        'annulled_operations' => [
            ['time' => '13:15:44', 'type' => 'Retiro', 'amount' => 'S/ 450.00', 'reason' => 'Error de monto'],
            ['time' => '14:10:45', 'type' => 'Deposito', 'amount' => 'S/ 2,750.00', 'reason' => 'Error de monto'],
        ],
    ],
    'confirmed' => [
        'id' => 2,
        'status' => 'CONFIRMADO',
        'status_badge' => 'success',
        'date' => '23 Oct 2023',
        'store' => 'Tienda Centro',
        'bank' => 'BCP',
        'agent' => 'ID: 00124 (M. Gomez)',
        'confirming_user' => 'Maria Garcia',
        'metrics' => [
            'total_ops' => 128,
            'gross_amount' => 'S/ 38,450.00',
            'total_entradas' => 'S/ 28,300.00',
            'total_salidas' => 'S/ 10,150.00',
            'net_movement' => '+S/ 18,150.00',
        ],
        'by_type' => [
            ['type' => 'Depositos', 'count' => 72, 'entradas' => 'S/ 22,000.00', 'salidas' => '-'],
            ['type' => 'Retiros', 'count' => 38, 'entradas' => '-', 'salidas' => 'S/ 9,800.00'],
            ['type' => 'Pago de Servicios', 'count' => 18, 'entradas' => 'S/ 6,300.00', 'salidas' => '-'],
        ],
        'by_worker' => [
            ['name' => 'Carlos Garcia', 'initials' => 'CG', 'ops' => 58, 'amount' => 'S/ 17,200.00'],
            ['name' => 'Laura Martinez', 'initials' => 'LM', 'ops' => 70, 'amount' => 'S/ 21,250.00'],
        ],
    ],
    'reopened' => [
        'id' => 3,
        'status' => 'REABIERTO',
        'status_badge' => 'warning',
        'date' => '22 Oct 2023',
        'store' => 'Tienda Centro',
        'bank' => 'BCP',
        'agent' => 'ID: 00124 (M. Gomez)',
        'reopen_reason' => 'Correccion de montos por parte del administrador',
        'metrics' => [
            'total_ops' => 135,
            'gross_amount' => 'S/ 41,200.00',
            'total_entradas' => 'S/ 29,500.00',
            'total_salidas' => 'S/ 11,700.00',
            'net_movement' => '+S/ 17,800.00',
        ],
        'by_type' => [
            ['type' => 'Depositos', 'count' => 78, 'entradas' => 'S/ 25,000.00', 'salidas' => '-'],
            ['type' => 'Retiros', 'count' => 40, 'entradas' => '-', 'salidas' => 'S/ 11,200.00'],
            ['type' => 'Pago de Servicios', 'count' => 17, 'entradas' => 'S/ 4,500.00', 'salidas' => '-'],
        ],
        'by_worker' => [
            ['name' => 'Carlos Garcia', 'initials' => 'CG', 'ops' => 65, 'amount' => 'S/ 20,000.00'],
            ['name' => 'Laura Martinez', 'initials' => 'LM', 'ops' => 70, 'amount' => 'S/ 21,200.00'],
        ],
    ],
];
