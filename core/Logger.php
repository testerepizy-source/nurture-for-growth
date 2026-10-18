<?php

namespace Core;

class Logger
{
    private static function write(string $level, string $message): void
    {
        $dir = __DIR__ . '/../storage/logs';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $level, $message);
        file_put_contents($dir . '/app.log', $line, FILE_APPEND);
    }

    public static function info(string $message): void  { self::write('INFO', $message); }
    public static function error(string $message): void { self::write('ERROR', $message); }
    public static function warning(string $message): void { self::write('WARNING', $message); }
}
