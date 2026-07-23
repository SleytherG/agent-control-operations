<?php

return [
    'metrics' => [
        'operation_count' => 142,
        'operation_count_trend' => '+12% vs ayer',
        'gross_amount' => 'S/ 45,230.50',
        'gross_amount_trend' => '+5.4% vs ayer',
        'cash_in' => 'S/ 32,150.00',
        'cash_in_ops' => 98,
        'cash_out' => 'S/ 13,080.50',
        'cash_out_ops' => 44,
        'net_movement' => 'S/ 19,069.50',
        'net_label' => 'Positivo',
    ],
    'distribution' => [
        ['type' => 'Depósitos', 'count' => 74, 'percentage' => 52],
        ['type' => 'Retiros', 'count' => 44, 'percentage' => 31],
        ['type' => 'Pago Servicios', 'count' => 24, 'percentage' => 17],
    ],
    'evolution' => [
        'labels' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00'],
        'entradas' => [12, 19, 25, 22, 15, 10, 8],
        'salidas' => [5, 8, 15, 12, 8, 4, 3],
    ],
    'recent_operations' => [
        ['time' => '14:28:12', 'bank' => 'BCP', 'agent' => 'Agente 00124', 'type' => 'Depósito', 'amount' => 'S/ 450.00', 'status' => 'active'],
        ['time' => '14:15:05', 'bank' => 'Interbank', 'agent' => 'Global Net', 'type' => 'Retiro', 'amount' => 'S/ 1,200.00', 'status' => 'active'],
        ['time' => '14:02:44', 'bank' => 'BCP', 'agent' => 'Agente 00124', 'type' => 'Pago Servicio', 'amount' => 'S/ 85.50', 'status' => 'active'],
        ['time' => '13:55:10', 'bank' => 'BBVA', 'agent' => 'Agente Express', 'type' => 'Depósito', 'amount' => 'S/ 3,500.00', 'status' => 'active'],
        ['time' => '13:40:22', 'bank' => 'BCP', 'agent' => 'Agente 00124', 'type' => 'Retiro', 'amount' => 'S/ 200.00', 'status' => 'active'],
    ],
];
