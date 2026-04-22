<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SentientCoach\Utils\Router;
use SentientCoach\Controllers\HomeController;
use SentientCoach\Controllers\QuestionController;
use SentientCoach\Controllers\QuestionnaireController;
use SentientCoach\Controllers\AiChatController;
use SentientCoach\Controllers\PlanController;
use SentientCoach\Services\OpenAIService;
use SentientCoach\Services\QuestionService;
use SentientCoach\Services\PlanService;
use SentientCoach\Utils\ExportService;

// Error reporting and headers
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Start session
session_start();

// Load configuration
$config = require __DIR__ . '/../config/app.php';

try {
    // Initialize services
    $openAIService = new OpenAIService($config['openai']);
    $questionService = new QuestionService($openAIService);
    $planService = new PlanService($openAIService);
    $exportService = new ExportService();

    // Initialize controllers
    $homeController = new HomeController($questionService, $planService);
    $questionController = new QuestionController($questionService);
    $questionnaireController = new QuestionnaireController();
    $aiChatController = new AiChatController($openAIService);
    $planController = new PlanController($planService, $questionService, $exportService);

    // Initialize router
    $router = new Router();

    // Define routes
    
    // Main pages
    $router->get('/', [$homeController, 'index']);
    $router->get('/questions', [$homeController, 'questions']);
    $router->get('/questionnaire', [$homeController, 'questionnaire']);
    $router->get('/ai-chat', [$homeController, 'aiChat']);
    $router->get('/plan', [$homeController, 'plan']);
    $router->post('/reset', [$homeController, 'reset']);

    // API routes for questions
    $router->post('/api/generate-question', [$questionController, 'generateQuestion']);
    $router->post('/api/submit-answer', [$questionController, 'submitAnswer']);
    $router->get('/api/progress', [$questionController, 'getProgress']);

    // API routes for questionnaire
    $router->post('/api/questionnaire/question', [$questionnaireController, 'getQuestion']);
    $router->post('/api/questionnaire/submit', [$questionnaireController, 'submitAnswer']);
    $router->get('/api/questionnaire/progress', [$questionnaireController, 'getProgress']);
    $router->post('/api/questionnaire/reset', [$questionnaireController, 'reset']);
    $router->get('/api/questionnaire/debug', [$questionnaireController, 'debug']);
    $router->get('/api/questionnaire/test', [$questionnaireController, 'testFlow']);

    // API routes for AI chat
    $router->post('/api/ai-chat/message', [$aiChatController, 'getNextMessage']);
    $router->post('/api/ai-chat/send', [$aiChatController, 'sendMessage']);
    $router->get('/api/ai-chat/progress', [$aiChatController, 'getProgress']);
    $router->post('/api/ai-chat/reset', [$aiChatController, 'reset']);

    // API routes for plans
    $router->post('/api/generate-plan', [$planController, 'generatePlan']);
    $router->post('/api/regenerate-plan', [$planController, 'regeneratePlan']);
    $router->get('/api/current-plan', [$planController, 'getCurrentPlan']);
    $router->get('/api/export/{format}', [$planController, 'exportPlan']);

    // Log endpoint for frontend logging
    $router->post('/api/log', function() {
        $input = json_decode(file_get_contents('php://input'), true);
        $message = $input['message'] ?? 'No message';
        $debugFile = __DIR__ . '/../logs/debug.log';
        file_put_contents($debugFile, $message . PHP_EOL, FILE_APPEND);
        echo json_encode(['success' => true]);
    });

    // Dispatch the request
    $router->dispatch();

} catch (Throwable $e) {
    // Log error
    error_log('Application error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
    // Return generic error
    http_response_code(500);
    
    if ($_SERVER['HTTP_ACCEPT'] && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Internal server error']);
    } else {
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Server Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error { color: #e53e3e; }
    </style>
</head>
<body>
    <h1 class="error">Server Error</h1>
    <p>We apologize for the inconvenience. Please try again later.</p>
    <a href="/">Return Home</a>
</body>
</html>';
    }
} 