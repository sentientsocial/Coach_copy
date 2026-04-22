<?php
session_start();

echo "Session Data:\n";
echo "=============\n";

if (isset($_SESSION['meditation_plan'])) {
    echo "Plan exists in session\n";
    echo "Plan data: " . json_encode($_SESSION['meditation_plan'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No plan found in session\n";
}

if (isset($_SESSION['questionnaire_answers'])) {
    echo "\nQuestionnaire answers exist\n";
    echo "Answer count: " . count($_SESSION['questionnaire_answers']) . "\n";
    echo "Answer keys: " . implode(',', array_keys($_SESSION['questionnaire_answers'])) . "\n";
} else {
    echo "\nNo questionnaire answers found\n";
}

if (isset($_SESSION['ai_chat_conversation'])) {
    echo "\nAI chat conversation exists\n";
    echo "Message count: " . count($_SESSION['ai_chat_conversation']) . "\n";
} else {
    echo "\nNo AI chat conversation found\n";
}

echo "\nAll session keys: " . implode(', ', array_keys($_SESSION)) . "\n";
