<?php

namespace App\Modules\DailyClosing\Application\Actions;

use App\Modules\DailyClosing\Models\DailyClosure;
use App\Modules\Operations\Models\Operation;
use Illuminate\Support\Facades\DB;

class CalculateClosing
{
    public function execute(DailyClosure $closure): DailyClosure
    {
        $startDate = $closure->business_date->format('Y-m-d') . ' 00:00:00';
        $endDate = $closure->business_date->addDay()->format('Y-m-d') . ' 00:00:00';

        $metrics = DB::table('operations as o')
            ->join('operation_types as ot', 'o.operation_type_id', '=', 'ot.id')
            ->where('o.agent_id', $closure->agent_id)
            ->where('o.status', Operation::STATUS_ACTIVE)
            ->where('o.effective_at', '>=', $startDate)
            ->where('o.effective_at', '<', $endDate)
            ->selectRaw('
                COUNT(*) as operation_count,
                COALESCE(SUM(o.amount), 0) as gross_amount,
                COALESCE(SUM(CASE WHEN o.cash_delta > 0 THEN o.cash_delta ELSE 0 END), 0) as total_cash_in,
                COALESCE(SUM(CASE WHEN o.cash_delta < 0 THEN ABS(o.cash_delta) ELSE 0 END), 0) as total_cash_out,
                COALESCE(SUM(CASE WHEN o.digital_delta > 0 THEN o.digital_delta ELSE 0 END), 0) as total_digital_in,
                COALESCE(SUM(CASE WHEN o.digital_delta < 0 THEN ABS(o.digital_delta) ELSE 0 END), 0) as total_digital_out,
                COALESCE(SUM(CASE WHEN ot.cash_multiplier = 0 AND ot.digital_multiplier = 0 THEN 1 ELSE 0 END), 0) as incomplete_count
            ')
            ->first();

        $m = $metrics;

        $expectedCash = bcadd(
            (string) $closure->opening_cash,
            bcsub((string) $m->total_cash_in, (string) $m->total_cash_out, 2),
            2
        );

        $expectedDigital = bcadd(
            (string) $closure->opening_digital,
            bcsub((string) $m->total_digital_in, (string) $m->total_digital_out, 2),
            2
        );

        $cashDifference = $closure->actual_closing_cash !== null
            ? bcsub((string) $closure->actual_closing_cash, $expectedCash, 2)
            : null;

        $digitalDifference = $closure->actual_closing_digital !== null
            ? bcsub((string) $closure->actual_closing_digital, $expectedDigital, 2)
            : null;

        $closure->update([
            'operation_count' => $m->operation_count,
            'gross_amount' => $m->gross_amount,
            'total_cash_in' => $m->total_cash_in,
            'total_cash_out' => $m->total_cash_out,
            'total_digital_in' => $m->total_digital_in,
            'total_digital_out' => $m->total_digital_out,
            'expected_closing_cash' => $expectedCash,
            'expected_closing_digital' => $expectedDigital,
            'cash_difference' => $cashDifference,
            'digital_difference' => $digitalDifference,
            'has_inconsistencies' => ($m->incomplete_count ?? 0) > 0,
        ]);

        return $closure->fresh();
    }
}
