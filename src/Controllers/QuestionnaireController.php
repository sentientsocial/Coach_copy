<?php

namespace SentientCoach\Controllers;

use SentientCoach\Utils\Response;

class QuestionnaireController
{
    private array $predefinedQuestions;
    
    public function __construct()
    {
        $this->loadPredefinedQuestions();
    }
    
    private function loadPredefinedQuestions(): void
    {
        $this->predefinedQuestions = [
            1 => [
                'title' => 'What is your primary goal for starting a meditation practice?',
                'options' => [
                    'Stress reduction',
                    'Improved sleep',
                    'Focus',
                    'Self-development and higher consciousness',
                    'Decrease anxiety',
                    'Take on a new challenge'
                ]
            ],
            2 => [
                'title' => 'How many minutes a week would you like to commit to your meditation practice?',
                'options' => [
                    '5min',
                    '10 mins',
                    '15min',
                    '30mins',
                    '1 hour a week'
                ]
            ],
            3 => [
                'title' => 'How many rest days a week would you like to include?',
                'options' => ['1', '2', '3', '4', '5', '6']
            ],
            4 => [
                'title' => 'Do you prefer guided meditation, self-guided meditation, or both?',
                'options' => [
                    'Guided meditation',
                    'Self-guided meditation', 
                    'Both'
                ]
            ],
            5 => [
                'title' => 'Where do you currently find your meditation content or support?',
                'options' => [
                    'App',
                    'Religious community',
                    'Meditation or Yoga studio',
                    'Online'
                ]
            ],
            6 => [
                'title' => 'Do you have a preferred tradition or type of meditation?',
                'options' => [
                    'Zen',
                    'Dzogchen',
                    'Vedanta',
                    'Sufi',
                    'Yogic',
                    'Mindfulness based stress reduction',
                    'Kabbalistic',
                    'Daoist'
                ]
            ],
            7 => [
                'title' => 'How comfortable are you with meditation techniques or practices?',
                'options' => [
                    'Beginner',
                    'Intermediate',
                    'Advanced',
                    'Just started'
                ]
            ],
            '7.5' => [
                'title' => 'What has been the most difficult part of meditating, or of sustaining the practice?',
                'options' => [], // Empty options means this is a text-only question
                'textOnly' => true, // Flag to indicate this is a text-only question
                'conditional' => [
                    'dependsOn' => 7,
                    'excludeAnswers' => ['Beginner'] // Skip this question if Q7 answer is "Beginner"
                ]
            ],
            8 => [
                'title' => 'What specific challenges do you face when trying to meditate?',
                'options' => [
                    'Difficulty focusing',
                    'Finding time',
                    'Feeling self-critical',
                    'Physical discomfort',
                    'Low motivation'
                ]
            ]
        ];
    }
    
    private function shouldShowConditionalQuestion($questionNumber): bool
    {
        error_log("shouldShowConditionalQuestion called for question: $questionNumber");
        
        // Check if the requested question should be shown based on conditional logic
        if (isset($this->predefinedQuestions[$questionNumber])) {
            $question = $this->predefinedQuestions[$questionNumber];
            
            // Check if this question has conditional logic
            if (isset($question['conditional'])) {
                $conditional = $question['conditional'];
                $dependsOnQuestion = $conditional['dependsOn'];
                $excludeAnswers = $conditional['excludeAnswers'] ?? [];
                
                error_log("Question $questionNumber is conditional, depends on question $dependsOnQuestion");
                error_log("Exclude answers: " . implode(', ', $excludeAnswers));
                
                // Check if the dependent question has been answered
                if (isset($_SESSION['questionnaire_answers'][$dependsOnQuestion])) {
                    $dependentAnswer = $_SESSION['questionnaire_answers'][$dependsOnQuestion]['answer'];
                    
                    error_log("Dependent question $dependsOnQuestion answered: '$dependentAnswer'");
                    error_log("Exclude answers array: " . json_encode($excludeAnswers));
                    error_log("Is '$dependentAnswer' in exclude array? " . (in_array($dependentAnswer, $excludeAnswers) ? 'YES' : 'NO'));
                    
                    // If the dependent answer is in the exclude list, don't show this question
                    if (in_array($dependentAnswer, $excludeAnswers)) {
                        error_log("Answer '$dependentAnswer' is in exclude list, NOT showing question $questionNumber");
                        return false;
                    }
                    // If dependent answer is not in exclude list, show the question
                    error_log("Answer '$dependentAnswer' is NOT in exclude list, SHOWING question $questionNumber");
                    return true;
                } else {
                    // If the dependent question hasn't been answered yet, don't show conditional questions
                    error_log("Dependent question $dependsOnQuestion not answered yet, NOT showing question $questionNumber");
                    return false;
                }
            } else {
                error_log("Question $questionNumber is NOT conditional, showing by default");
            }
        }
        
        return true; // Show the question by default (for non-conditional questions)
    }
    
    private function calculateTotalQuestions(): int
    {
        $totalQuestions = 0;
        $questionOrder = [1, 2, 3, 4, 5, 6, 7, '7.5', 8];
        
        // Count all questions that should be shown
        foreach ($questionOrder as $qNum) {
            if ($this->shouldShowConditionalQuestion($qNum)) {
                $totalQuestions++;
            }
        }
        
        return $totalQuestions;
    }
    
    private function getQuestionIndex($questionNumber): int
    {
        $index = 1;
        $questionOrder = [1, 2, 3, 4, 5, 6, 7, '7.5', 8];
        
        foreach ($questionOrder as $qNum) {
            if ($qNum == $questionNumber) {
                return $index;
            }
            
            // Check if this question should be included in the count
            if ($this->shouldShowConditionalQuestion($qNum)) {
                $index++;
            }
        }
        
        return $index;
    }
    
    private function getNextQuestionNumber($currentQuestion)
    {
        // Define the question order manually to handle mixed string/int keys
        $questionOrder = [1, 2, 3, 4, 5, 6, 7, '7.5', 8];
        
        error_log("getNextQuestionNumber called with currentQuestion: $currentQuestion");
        
        $foundCurrent = false;
        foreach ($questionOrder as $qNum) {
            if ($foundCurrent) {
                // Check if this next question should be shown
                $shouldShow = $this->shouldShowConditionalQuestion($qNum);
                error_log("Checking question $qNum: shouldShow = " . ($shouldShow ? 'true' : 'false'));
                
                if ($shouldShow) {
                    error_log("Returning next question: $qNum");
                    return $qNum;
                }
                // If not, continue to the next question
            }
            
            if ($qNum == $currentQuestion) {
                $foundCurrent = true;
                error_log("Found current question: $currentQuestion");
            }
        }
        
        // If no next question found, return something to indicate completion
        error_log("No next question found, returning 'complete'");
        return 'complete';
    }
    
    private function getPreviousQuestionNumber($currentQuestion)
    {
        // Define the question order manually to handle mixed string/int keys
        $questionOrder = [1, 2, 3, 4, 5, 6, 7, '7.5', 8];
        
        $foundCurrent = false;
        $previousValidQuestion = null;
        
        foreach ($questionOrder as $qNum) {
            if ($qNum == $currentQuestion) {
                return $previousValidQuestion; // Return the last valid question we found
            }
            
            // Check if this question should be shown
            if ($this->shouldShowConditionalQuestion($qNum)) {
                $previousValidQuestion = $qNum;
            }
        }
        
        // If no previous question found, return null
        return null;
    }
    
    public function getQuestion(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $questionNumber = $input['questionNumber'] ?? 1;
            
            // Validate question number
            if (!isset($this->predefinedQuestions[$questionNumber])) {
                Response::error('Invalid question number', 400);
                return;
            }
            
            // Check if this conditional question should be shown
            if (!$this->shouldShowConditionalQuestion($questionNumber)) {
                error_log("Question $questionNumber blocked. Session Q6 answer: " . ($_SESSION['questionnaire_answers'][6]['answer'] ?? 'none'));
                Response::error('Question not available', 400);
                return;
            }
            
            $question = $this->predefinedQuestions[$questionNumber];
            
            $totalQuestions = $this->calculateTotalQuestions();
            $previousQuestion = $this->getPreviousQuestionNumber($questionNumber);
            
            $response = [
                'questionNumber' => $questionNumber,
                'title' => $question['title'],
                'totalQuestions' => $totalQuestions,
                'progress' => round(($this->getQuestionIndex($questionNumber) - 1) / $totalQuestions * 100),
                'allowCustomAnswer' => true, // Allow users to write their own answers
                'previousQuestion' => $previousQuestion
            ];

            // Handle text-only questions
            if (isset($question['textOnly']) && $question['textOnly']) {
                $response['textOnly'] = true;
                $response['options'] = []; // No predefined options for text-only questions
            }
            // Handle multi-select questions (Question 3)
            else if (isset($question['multiSelect']) && $question['multiSelect']) {
                $response['multiSelect'] = true;
                $response['groups'] = $question['groups'];
            } else {
                $response['options'] = $question['options'];
            }
            
            // Include previous answer if it exists (for back navigation)
            if (isset($_SESSION['questionnaire_answers'][$questionNumber])) {
                $response['previousAnswer'] = $_SESSION['questionnaire_answers'][$questionNumber]['answer'];
                $debugFile = __DIR__ . '/../../logs/debug.log';
                file_put_contents($debugFile, "Question $questionNumber: Found previous answer: " . $response['previousAnswer'] . PHP_EOL, FILE_APPEND);
            } else {
                $debugFile = __DIR__ . '/../../logs/debug.log';
                file_put_contents($debugFile, "Question $questionNumber: No previous answer found" . PHP_EOL, FILE_APPEND);
            }
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('Questionnaire question error: ' . $e->getMessage());
            Response::error('Unable to get question. Please try again.', 500);
        }
    }
    
    public function submitAnswer(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $requiredFields = ['questionNumber', 'answer'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field])) {
                    Response::error("Missing required field: {$field}", 400);
                    return;
                }
            }
            
            $questionNumber = $input['questionNumber']; // Allow string questions like "6.5"
            $answer = trim($input['answer']);
            $isCustomAnswer = $input['isCustomAnswer'] ?? false;
            
            // Validate question number
            if (!isset($this->predefinedQuestions[$questionNumber])) {
                Response::error('Invalid question number', 400);
                return;
            }
            
            // Validate answer is not empty
            if (empty($answer)) {
                Response::error('Answer cannot be empty', 400);
                return;
            }
            
            // Store answer in session
            if (!isset($_SESSION['questionnaire_answers'])) {
                $_SESSION['questionnaire_answers'] = [];
            }
            
            $_SESSION['questionnaire_answers'][$questionNumber] = [
                'questionNumber' => $questionNumber,
                'question' => $this->predefinedQuestions[$questionNumber]['title'],
                'answer' => $answer,
                'isCustomAnswer' => $isCustomAnswer,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Determine next question and if questionnaire is complete
            $debugFile = __DIR__ . '/../../logs/debug.log';
            file_put_contents($debugFile, "\n=== DEBUG submitAnswer for Q$questionNumber ===" . PHP_EOL, FILE_APPEND);
            file_put_contents($debugFile, "Answer received: '$answer'" . PHP_EOL, FILE_APPEND);
            file_put_contents($debugFile, "Session after storing answer: " . json_encode($_SESSION['questionnaire_answers']) . PHP_EOL, FILE_APPEND);
            
            if ($questionNumber == 7) {
                $shouldShow75 = $this->shouldShowConditionalQuestion('7.5');
                file_put_contents($debugFile, "Should show Q7.5? " . ($shouldShow75 ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
            }
            
            $nextQuestion = $this->getNextQuestionNumber($questionNumber);
            file_put_contents($debugFile, "Next question calculated: $nextQuestion" . PHP_EOL, FILE_APPEND);
            
            $isComplete = ($nextQuestion === 'complete');
            $totalQuestions = $this->calculateTotalQuestions();
            
            $response = [
                'success' => true,
                'questionNumber' => $questionNumber,
                'nextQuestion' => $nextQuestion,
                'isComplete' => $isComplete,
                'progress' => round(count($_SESSION['questionnaire_answers']) / $totalQuestions * 100)
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('Questionnaire answer submission error: ' . $e->getMessage());
            Response::error('Unable to save answer. Please try again.', 500);
        }
    }
    
    public function getProgress(): void
    {
        try {
            $answers = $_SESSION['questionnaire_answers'] ?? [];
            $answeredCount = count($answers);
            
            // Calculate current question based on highest answered question
            $currentQuestion = 1;
            if (!empty($answers)) {
                $questionNumbers = array_keys($answers);
                $maxAnswered = max($questionNumbers);
                
                // Use the conditional logic to determine the next question
                $nextQuestion = $this->getNextQuestionNumber($maxAnswered);
                if ($nextQuestion === 'complete') {
                    $currentQuestion = 8; // Indicates completion
                    $isComplete = true;
                } else {
                    $currentQuestion = $nextQuestion;
                    $isComplete = false;
                }
            } else {
                $isComplete = false;
            }
            
            $totalQuestions = $this->calculateTotalQuestions();
            
            $response = [
                'currentQuestion' => $currentQuestion,
                'totalQuestions' => $totalQuestions,
                'progress' => round($answeredCount / $totalQuestions * 100),
                'answersCount' => $answeredCount,
                'isComplete' => $isComplete,
                'answers' => array_values($answers)
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('Questionnaire progress error: ' . $e->getMessage());
            Response::error('Unable to get progress. Please try again.', 500);
        }
    }
    
    public function reset(): void
    {
        try {
            // Clear questionnaire answers
            unset($_SESSION['questionnaire_answers']);
            
            Response::json(['success' => true, 'message' => 'Questionnaire reset successfully']);
        } catch (\Exception $e) {
            error_log('Questionnaire reset error: ' . $e->getMessage());
            Response::error('Unable to reset questionnaire. Please try again.', 500);
        }
    }

    public function debug(): void
    {
        try {
            $sessionData = $_SESSION['questionnaire_answers'] ?? [];
            $question6Answer = $sessionData[6]['answer'] ?? 'Not answered';
            
            // Test the conditional logic for Question 6.5
            $shouldShow65 = $this->shouldShowConditionalQuestion('6.5');
            
            // Test each step of the logic manually
            $q65Definition = $this->predefinedQuestions['6.5'] ?? null;
            $isConditional = isset($q65Definition['conditional']);
            $excludeAnswers = $q65Definition['conditional']['excludeAnswers'] ?? [];
            $inExcludeList = in_array($question6Answer, $excludeAnswers);
            
            $response = [
                'question6Answer' => $question6Answer,
                'shouldShowQuestion65' => $shouldShow65,
                'sessionData' => $sessionData,
                'nextQuestionAfter6' => $this->getNextQuestionNumber(6),
                'debugInfo' => [
                    'q65IsConditional' => $isConditional,
                    'excludeAnswers' => $excludeAnswers,
                    'question6AnswerInExcludeList' => $inExcludeList,
                    'q65Definition' => $q65Definition
                ]
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('Debug error: ' . $e->getMessage());
            Response::error('Debug failed', 500);
        }
    }

    public function testFlow(): void
    {
        try {
            // Simulate the exact scenario
            $_SESSION['questionnaire_answers'] = [
                6 => [
                    'questionNumber' => 6,
                    'question' => 'How comfortable are you with meditation techniques or practices?',
                    'answer' => 'Beginner',
                    'isCustomAnswer' => false,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Test the conditional logic step by step
            $q65Definition = $this->predefinedQuestions['6.5'];
            $conditional = $q65Definition['conditional'];
            $dependsOnQuestion = $conditional['dependsOn']; // Should be 6
            $excludeAnswers = $conditional['excludeAnswers']; // Should be ['Beginner']
            
            // Check if Question 6 has been answered
            $question6Answered = isset($_SESSION['questionnaire_answers'][$dependsOnQuestion]);
            $question6Answer = $question6Answered ? $_SESSION['questionnaire_answers'][$dependsOnQuestion]['answer'] : 'NOT_ANSWERED';
            
            // Check if the answer is in exclude list
            $inExcludeList = in_array($question6Answer, $excludeAnswers);
            
            $shouldShow65 = $this->shouldShowConditionalQuestion('6.5');
            $nextAfter6 = $this->getNextQuestionNumber(6);
            
            $response = [
                'test_scenario' => 'Question 6 answered as Beginner - Step by step debug',
                'session_data' => $_SESSION['questionnaire_answers'],
                'q65_depends_on' => $dependsOnQuestion,
                'exclude_answers' => $excludeAnswers,
                'question6_answered' => $question6Answered,
                'question6_answer' => $question6Answer,
                'answer_in_exclude_list' => $inExcludeList,
                'should_show_6_5' => $shouldShow65,
                'next_question_after_6' => $nextAfter6,
                'expected_result' => [
                    'should_show_6_5' => false,
                    'next_question_after_6' => 7
                ]
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            Response::error('Test failed: ' . $e->getMessage(), 500);
        }
    }
} 