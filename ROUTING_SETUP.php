<?php
$router->get('/wellness', function(array $params) {
    $controller = new \App\Controllers\WellnessController();
    $controller->index();
});

// =====================================================================
// 2. CONSULTATION RESCHEDULE
// =====================================================================

// GET: Display reschedule request form
$router->get('/consultation/{id}/reschedule', function(array $params) {
    $controller = new \App\Controllers\ConsultationController();
    $controller->rescheduleRequest($params);
});

// POST: Submit reschedule request
$router->post('/consultation/{id}/reschedule', function(array $params) {
    $controller = new \App\Controllers\ConsultationController();
    $controller->rescheduleStore($params);
});

// =====================================================================
// 3. CONSULTATION EXTEND
// =====================================================================

// GET: Display extend duration form
$router->get('/consultation/{id}/extend', function(array $params) {
    $controller = new \App\Controllers\ConsultationController();
    $controller->extendRequest($params);
});

// POST: Submit extend request
$router->post('/consultation/{id}/extend', function(array $params) {
    $controller = new \App\Controllers\ConsultationController();
    $controller->extendStore($params);
});

// =====================================================================
// 4. MULTI-PAYMENT METHODS
// =====================================================================

// ---- CONSULTATION PAYMENT ----

// GET: Display payment methods for consultation
$router->get('/payment/consultation/{id}', function(array $params) {
    $controller = new \App\Controllers\PaymentController();
    $controller->consultationPayment($params);
});

// POST: Process consultation payment
$router->post('/consultation/{id}/payment/process', function(array $params) {
    $controller = new \App\Controllers\PaymentController();
    $controller->processConsultationPayment($params);
});

// ---- RESCHEDULE PAYMENT ----

// GET: Display payment methods for reschedule
$router->get('/payment/reschedule/{id}', function(array $params) {
    $controller = new \App\Controllers\PaymentController();
    $controller->reschedulePayment($params);
});

// POST: Process reschedule payment
$router->post('/reschedule/{id}/payment/process', function(array $params) {
    $controller = new \App\Controllers\PaymentController();
    $controller->processReschedulePayment($params);
});

// ---- EXTENSION PAYMENT ----

// GET: Display payment methods for extension
$router->get('/payment/extension/{id}', function(array $params) {
    $controller = new \App\Controllers\PaymentController();
    $controller->extensionPayment($params);
});

// POST: Process extension payment
$router->post('/extension/{id}/payment/process', function(array $params) {
    $controller = new \App\Controllers\PaymentController();
    $controller->processExtensionPayment($params);
});
