<?php
require_once __DIR__ . '/vendor/autoload.php';

// Start session like the main app
session_start();

// Load configuration
$config = require __DIR__ . '/config/app.php';

echo "Session Debug Info\n";
echo "==================\n";

if (isset($_SESSION['meditation_plan'])) {
    echo "Plan exists in session\n";
    $plan = $_SESSION['meditation_plan'];
    echo "Plan title: " . ($plan['title'] ?? 'No title') . "\n";
    echo "Plan has schedule: " . (isset($plan['schedule']) ? 'Yes (' . count($plan['schedule']) . ' days)' : 'No') . "\n";
} else {
    echo "No plan found in session\n";
}

if (isset($_SESSION['questionnaire_answers'])) {
    echo "\nQuestionnaire answers exist\n";
    echo "Answer count: " . count($_SESSION['questionnaire_answers']) . "\n";
    $answers = $_SESSION['questionnaire_answers'];
    foreach ($answers as $key => $answer) {
        echo "Q{$key}: " . ($answer['answer'] ?? 'No answer') . "\n";
    }
} else {
    echo "\nNo questionnaire answers found\n";
}

// Test PlanService directly
use SentientCoach\Services\OpenAIService;
use SentientCoach\Services\PlanService;

try {
    $openAIService = new OpenAIService($config['openai']);
    $planService = new PlanService($openAIService);
    
    echo "\nTesting PlanService::getCurrentPlan()\n";
    $currentPlan = $planService->getCurrentPlan();
    
    if ($currentPlan) {
        echo "PlanService found plan: " . $currentPlan->title . "\n";
    } else {
        echo "PlanService found no plan\n";
    }
} catch (Exception $e) {
    echo "Error testing PlanService: " . $e->getMessage() . "\n";
}

echo "\nSession ID: " . session_id() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "All session keys: " . implode(', ', array_keys($_SESSION)) . "\n";
