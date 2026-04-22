<?php

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

return [
    'app' => [
        'name' => 'Sentient AI Coach',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],
    
    'openai' => [
        'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        'models' => [
            'questions' => 'gpt-5',
            'plans' => 'gpt-5',
            'chat' => 'gpt-5'
        ],
        'temperature' => [
            'questions' => 0.5,
            'plans' => 0.7,
            'chat' => 0.8
        ]
    ],
    
    'session' => [
        'name' => $_ENV['SESSION_NAME'] ?? 'sentient_coach_session',
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200), // 2 hours
    ]
]; 