<?php

namespace Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . '/../app/Views/layouts/' . $layout . '.php';
        require $layoutFile;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . base_url($path));
        exit;
    }

    protected function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function requireCsrf(): void
    {
        if (!Csrf::verifyRequest()) {
            http_response_code(419);
            die('Sesi kedaluwarsa atau token keamanan tidak valid (CSRF). Silakan muat ulang halaman dan coba lagi.');
        }
    }
}
