<?php
// /routes/api.php

use Controllers\TicketController;
use Core\Response;

// Get the requested URI and method
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Define the API base path
$apiBase = '/TMTicket/api';

// Routing logic
try {
    switch (true) {
        // Ticket endpoints
        case ($uri === $apiBase . '/ticket/step1' && $method === 'POST'):
            TicketController::step1();
            break;

        case ($uri === $apiBase . '/ticket/step2' && $method === 'POST'):
            TicketController::step2();
            break;

        case ($uri === $apiBase . '/ticket/step3' && $method === 'POST'):
            TicketController::step3();
            break;

        // API health check
        case ($uri === $apiBase . '/health' && $method === 'GET'):
            Response::json(['status' => 'ok', 'message' => 'API is running']);
            break;

        // Default: 404 Not Found
        default:
            Response::notFound();
            break;
    }
} catch (Exception $e) {
    // Log the error
    error_log('Route error: ' . $e->getMessage());

    // Return a generic error response
    Response::error('An internal server error occurred', 500);
}
