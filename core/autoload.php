<?php
/**
 * Minimal PSR-4 style autoloader (no Composer dependency required).
 * Namespace map:
 *   Core\      -> core/
 *   App\       -> app/
 */
spl_autoload_register(function (string $class) {
    $map = [
        'Core\\' => __DIR__ . '/',
        'App\\'  => __DIR__ . '/../app/',
    ];
    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});
