<?php

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class AuthTransactionRunner
{
    private int $maxRetries;
    private int $retryDelayMs;

    public function __construct()
    {
        $this->maxRetries = (int) config('session-security.transaction.max_retries', 3);
        $this->retryDelayMs = (int) config('session-security.transaction.retry_delay_ms', 100);
    }

    public function run(callable $operation): mixed
    {
        $attempt = 0;

        while (true) {
            try {
                return DB::transaction($operation);
            } catch (Throwable $e) {
                $attempt++;
                if ($attempt > $this->maxRetries || ! $this->isTransientError($e)) {
                    throw $e;
                }
                usleep($this->retryDelayMs * 1000);
            }
        }
    }

    private function isTransientError(Throwable $e): bool
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if (str_contains($message, 'Deadlock') || str_contains($message, 'Lock wait timeout')) {
            return true;
        }

        $pgTransientCodes = ['40P01', '55P03', '40001', '40P02'];

        return in_array($code, $pgTransientCodes, true);
    }
}
