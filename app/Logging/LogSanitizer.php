<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FormattableHandlerInterface;

class LogSanitizer
{
    private const SENSITIVE_KEYS = [
        'password', 'token', 'jwt', 'hash', 'secret',
        'access_token', 'refresh_token', 'signing_key',
        'pepper', '__Host-', 'authorization',
    ];

    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new LineFormatter(
                    null,
                    null,
                    true,
                    true
                ));
            }
        }
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
