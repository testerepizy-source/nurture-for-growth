<?php
$publicPath = __DIR__ . '/public/index.php';
if (file_exists($publicPath)) {
    require $publicPath;
} else {
    http_response_code(404);
    echo 'Public index.php not found';
}
