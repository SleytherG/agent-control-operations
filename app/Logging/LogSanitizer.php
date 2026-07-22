<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogSanitizer
{
    private const SENSITIVE_KEYS = [
        'password', 'token', 'jwt', 'hash', 'secret',
        'access_token', 'refresh_token', 'signing_key',
        'pepper', '__Host-', 'authorization',
    ];

    public function __invoke($logger): void
    {
        $logger->pushProcessor(new class(self::SENSITIVE_KEYS) implements ProcessorInterface {
            public function __construct(private array $keys) {}

            public function __invoke(LogRecord $record): LogRecord
            {
                $context = $record->context;
                $extra = $record->extra;

                foreach ($this->keys as $key) {
                    foreach (['context', 'extra'] as $section) {
                        $arr = $section === 'context' ? $context : $extra;
                        if (array_key_exists($key, $arr)) {
                            if ($section === 'context') {
                                $context[$key] = '[REDACTED]';
                            } else {
                                $extra[$key] = '[REDACTED]';
                            }
                        }
                    }
                }

                return $record->with(context: $context, extra: $extra);
            }
        });
    }

    public static function sanitize(array $record): array
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            foreach (['context', 'extra'] as $section) {
                if (isset($record[$section][$key])) {
                    $record[$section][$key] = '[REDACTED]';
                }
            }
        }

        return $record;
    }
}
