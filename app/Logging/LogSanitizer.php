<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogSanitizer
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'admin_password',
        'token', 'access_token', 'refresh_token',
        'jwt', 'hash', 'secret', 'signing_key',
        'pepper', '__Host-', 'authorization',
        'temporary_password', 'temporaryPassword',
        'new_password', 'old_password',
    ];

    public function __invoke($logger): void
    {
        $logger->pushProcessor(new class(self::SENSITIVE_KEYS) implements ProcessorInterface {
            public function __construct(private array $keys) {}

            public function __invoke(LogRecord $record): LogRecord
            {
                $context = self::redactRecursive($record->context, $this->keys);
                $extra = self::redactRecursive($record->extra, $this->keys);

                return $record->with(context: $context, extra: $extra);
            }

            private static function redactRecursive(mixed $data, array $keys): mixed
            {
                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        if (is_string($key) && self::isSensitive($key, $keys)) {
                            $data[$key] = '[REDACTED]';
                        } elseif (is_array($value) || is_object($value)) {
                            $data[$key] = self::redactRecursive($value, $keys);
                        }
                    }
                }

                return $data;
            }

            private static function isSensitive(string $key, array $keys): bool
            {
                $lowerKey = mb_strtolower($key);

                foreach ($keys as $sensitiveKey) {
                    $lowerSensitive = mb_strtolower($sensitiveKey);
                    if ($lowerKey === $lowerSensitive || str_contains($lowerKey, $lowerSensitive)) {
                        return true;
                    }
                }

                return false;
            }
        });
    }

    public static function sanitize(array $record): array
    {
        foreach (['context', 'extra'] as $section) {
            if (isset($record[$section]) && is_array($record[$section])) {
                $record[$section] = self::redactSection($record[$section]);
            }
        }

        return $record;
    }

    private static function redactSection(array $data): array
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            foreach ($data as $dataKey => $value) {
                if (is_string($dataKey) && mb_strtolower($dataKey) === mb_strtolower($key)) {
                    $data[$dataKey] = '[REDACTED]';
                } elseif (is_string($dataKey) && str_contains(mb_strtolower($dataKey), mb_strtolower($key))) {
                    $data[$dataKey] = '[REDACTED]';
                } elseif (is_array($value)) {
                    $data[$dataKey] = self::redactSection($value);
                }
            }
        }

        return $data;
    }
}
