<?php

namespace App\Modules\Operations\Services;

use App\Modules\Operations\Models\Operation;
use Carbon\Carbon;

class InternalCodeGenerator
{
    public function generate(string $effectiveDate): string
    {
        $datePart = Carbon::parse($effectiveDate)->format('Ymd');
        $prefix = 'OP-' . $datePart . '-';

        $last = Operation::where('internal_code', 'like', $prefix . '%')
            ->orderBy('internal_code', 'desc')
            ->lockForUpdate()
            ->first();

        $nextSequence = $last
            ? ((int) substr($last->internal_code, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}
