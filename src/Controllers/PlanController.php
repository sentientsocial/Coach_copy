<?php

namespace SentientCoach\Controllers;

use SentientCoach\Utils\Response;
use SentientCoach\Services\PlanService;
use SentientCoach\Services\QuestionService;
use SentientCoach\Utils\ExportService;

class PlanController
{
    private PlanService $planService;
    private QuestionService $questionService;
    private ExportService $exportService;
    
    public function __construct(PlanService $planService, QuestionService $questionService, ExportService $exportService)
    {
        $this->planService = $planService;
        $this->questionService = $questionService;
        $this->exportService = $exportService;
    }
    
    public function generatePlan(): void
    {
        try {
            // Check which flow was completed and get answers
            $answers = $this->getCompletedAnswers();
            
            if (empty($answers)) {
                Response::error('Please complete all questions first', 400);
                return;
            }
            
            $plan = $this->planService->generatePlan($answers);
            
            Response::json([
                'success' => true,
                'plan' => $plan->toArray()
            ]);
        } catch (\Exception $e) {
            error_log('Plan generation error: ' . $e->getMessage());
            Response::error('Unable to generate plan. Please try again.', 500);
        }
    }
    
    public function regeneratePlan(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['feedback'])) {
                Response::error('Feedback is required for plan regeneration', 400);
                return;
            }
            
            $feedback = trim($input['feedback']);
            $answers = $this->getCompletedAnswers();
            
            if (empty($answers)) {
                Response::error('No answers found. Please restart the questionnaire.', 400);
                return;
            }
            
            $plan = $this->planService->regeneratePlan($answers, $feedback);
            
            Response::json([
                'success' => true,
                'plan' => $plan->toArray()
            ]);
        } catch (\Exception $e) {
            error_log('Plan regeneration error: ' . $e->getMessage());
            Response::error('Unable to regenerate plan. Please try again.', 500);
        }
    }
    
    public function exportPlan(string $format): void
    {
        try {
            $plan = $this->planService->getCurrentPlan();
            
            if (!$plan) {
                Response::error('No plan found. Please generate a plan first.', 404);
                return;
            }
            
            $filename = 'meditation-plan-' . date('Y-m-d');
            
            switch ($format) {
                case 'calendar':
                case 'ics':
                    $content = $this->exportService->exportToCalendar($plan);
                    Response::download($content, $filename . '.ics', 'text/calendar');
                    break;
                    
                case 'html':
                    $content = $this->exportService->exportToHtml($plan);
                    Response::download($content, $filename . '.html', 'text/html');
                    break;
                    
                case 'text':
                case 'txt':
                    $content = $this->exportService->exportToText($plan);
                    Response::download($content, $filename . '.txt', 'text/plain');
                    break;
                    
                case 'json':
                    $content = $this->exportService->exportToJson($plan);
                    Response::download($content, $filename . '.json', 'application/json');
                    break;
                    
                default:
                    Response::error('Invalid export format. Supported formats: calendar, html, text, json, resources', 400);
                    return;
            }
        } catch (\Exception $e) {
            error_log('Plan export error: ' . $e->getMessage());
            Response::error('Unable to export plan. Please try again.', 500);
        }
    }
    
    public function getCurrentPlan(): void
    {
        try {
            $plan = $this->planService->getCurrentPlan();
            
            if (!$plan) {
                Response::json(['plan' => null]);
                return;
            }
            
            Response::json([
                'plan' => $plan->toArray()
            ]);
        } catch (\Exception $e) {
            error_log('Get plan error: ' . $e->getMessage());
            Response::error('Unable to retrieve plan. Please try again.', 500);
        }
    }
    
    private function getCompletedAnswers(): array
    {
        // Check for predefined questionnaire answers (7 questions + conditional Q6.5)
        if (isset($_SESSION['questionnaire_answers']) && count($_SESSION['questionnaire_answers']) >= 7) {
            return array_values($_SESSION['questionnaire_answers']);
        }
        
        // Check for AI chat conversation (minimum 6 exchanges for plan generation)
        if (isset($_SESSION['ai_chat_conversation']) && count($_SESSION['ai_chat_conversation']) >= 6) {
            // Check if conversation indicates readiness for plan
            $userMessages = array_filter($_SESSION['ai_chat_conversation'], fn($msg) => $msg['type'] === 'user');
            if (count($userMessages) >= 3) {
                return $this->convertConversationToAnswers($_SESSION['ai_chat_conversation']);
            }
        }
        
        // Check for legacy AI-generated questions answers
        if ($this->questionService->isQuestionnaireComplete()) {
            return $this->questionService->getUserAnswers();
        }
        
        return [];
    }
    
    private function convertConversationToAnswers(array $conversation): array
    {
        // Combine all user messages into a single text for analysis
        $allUserText = '';
        foreach ($conversation as $message) {
            if ($message['type'] === 'user') {
                $allUserText .= ' ' . $message['content'];
            }
        }
        $allUserText = strtolower($allUserText);
        
        $answers = [];
        
        // Question 1: Primary goal for meditation (aligned with AI chat questions)
        $goal = $this->extractGoal($allUserText);
        $answers[] = [
            'questionNumber' => 1,
            'question' => 'What brings you to meditation right now?',
            'answer' => $goal,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        // Question 2: Time commitment per day (aligned with AI chat questions)
        $timeCommitment = $this->extractTimeCommitment($allUserText);
        $answers[] = [
            'questionNumber' => 2,
            'question' => 'How much time can you dedicate per day — 5, 10, or 20 minutes?',
            'answer' => $timeCommitment,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        // Question 3: Frequency and guidance preference (aligned with AI chat questions)
        $frequency = $this->extractFrequency($allUserText);
        $guidance = $this->extractGuidancePreference($allUserText);
        $answers[] = [
            'questionNumber' => 3,
            'question' => 'Do you prefer guided meditations (someone speaking) or self-guided silence?',
            'answer' => $frequency . ' per week, ' . $guidance,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        // Question 4: Focus area (aligned with AI chat questions)
        $focus = $this->extractFocusArea($allUserText);
        $answers[] = [
            'questionNumber' => 4,
            'question' => 'What do you want to focus on: relaxation, focus, emotional balance, or spiritual growth?',
            'answer' => $focus,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        // Question 5: Experience level (aligned with AI chat questions)
        $experience = $this->extractExperienceLevel($allUserText);
        $answers[] = [
            'questionNumber' => 5,
            'question' => 'Have you meditated before? If yes, what worked or didn\'t?',
            'answer' => $experience,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        // Question 6: Current resources (additional context)
        $resources = $this->extractCurrentResources($allUserText);
        $answers[] = [
            'questionNumber' => 6,
            'question' => 'Where do you usually look for meditation content or support?',
            'answer' => $resources,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        // Question 7: Challenges (aligned with AI chat questions)
        $challenges = $this->extractChallenges($allUserText);
        $answers[] = [
            'questionNumber' => 7,
            'question' => 'What\'s been the biggest challenge in your journey so far?',
            'answer' => $challenges,
            'timestamp' => date('Y-m-d H:i:s'),
            'isCustom' => false,
            'optionId' => null
        ];
        
        return $answers;
    }
    
    private function extractGoal(string $text): string
    {
        $goals = [
            'stress' => 'Stress reduction',
            'anxiety' => 'Decrease anxiety',
            'sleep' => 'Improved sleep',
            'focus' => 'Focus',
            'spiritual' => 'Self-development and higher consciousness',
            'challenge' => 'Take on a new challenge',
            'self-development' => 'Self-development and higher consciousness',
            'consciousness' => 'Self-development and higher consciousness'
        ];
        
        foreach ($goals as $keyword => $goal) {
            if (strpos($text, $keyword) !== false) {
                return $goal;
            }
        }
        
        return 'Stress reduction'; // Default
    }
    
    private function extractTimeCommitment(string $text): string
    {
        if (strpos($text, '5 min') !== false || strpos($text, 'five min') !== false) return '5min';
        if (strpos($text, '10 min') !== false || strpos($text, 'ten min') !== false) return '10 mins';
        if (strpos($text, '15 min') !== false || strpos($text, 'fifteen min') !== false) return '15min';
        if (strpos($text, '30 min') !== false || strpos($text, 'thirty min') !== false || strpos($text, 'half hour') !== false) return '30mins';
        if (strpos($text, '1 hour') !== false || strpos($text, 'one hour') !== false || strpos($text, 'hour') !== false) return '1 hour a week';
        
        return '10 mins'; // Default
    }
    
    private function extractFrequency(string $text): string
    {
        if (strpos($text, '2 time') !== false || strpos($text, 'twice') !== false) return '2 times';
        if (strpos($text, '3 time') !== false || strpos($text, 'three time') !== false) return '3 times';
        if (strpos($text, '5 time') !== false || strpos($text, 'five time') !== false || strpos($text, 'daily') !== false || strpos($text, 'every day') !== false) return '5 times';
        
        return '3 times'; // Default
    }
    
    private function extractGuidancePreference(string $text): string
    {
        if (strpos($text, 'guided') !== false && strpos($text, 'self') !== false) return 'Both';
        if (strpos($text, 'guided') !== false) return 'Guided meditation';
        if (strpos($text, 'self') !== false || strpos($text, 'alone') !== false || strpos($text, 'independent') !== false) return 'Self-guided meditation';
        
        return 'Guided meditation'; // Default
    }
    
    private function extractFocusArea(string $text): string
    {
        $focusAreas = [
            'relaxation' => 'Relaxation',
            'stress' => 'Relaxation',
            'calm' => 'Relaxation',
            'focus' => 'Focus',
            'concentration' => 'Focus',
            'attention' => 'Focus',
            'emotional' => 'Emotional balance',
            'mood' => 'Emotional balance',
            'feelings' => 'Emotional balance',
            'spiritual' => 'Spiritual growth',
            'consciousness' => 'Spiritual growth',
            'enlightenment' => 'Spiritual growth',
            'mindfulness' => 'Focus',
            'awareness' => 'Focus'
        ];
        
        foreach ($focusAreas as $keyword => $focus) {
            if (strpos($text, $keyword) !== false) {
                return $focus;
            }
        }
        
        return 'Relaxation'; // Default
    }
    
    private function extractCurrentResources(string $text): string
    {
        if (strpos($text, 'app') !== false) return 'App';
        if (strpos($text, 'studio') !== false || strpos($text, 'yoga') !== false) return 'Meditation or Yoga studio';
        if (strpos($text, 'church') !== false || strpos($text, 'religious') !== false || strpos($text, 'temple') !== false) return 'Religious community';
        if (strpos($text, 'online') !== false || strpos($text, 'internet') !== false || strpos($text, 'youtube') !== false) return 'Online';
        
        return 'Online'; // Default
    }
    
    private function extractTradition(string $text): string
    {
        $traditions = [
            'zen' => 'Zen',
            'dzogchen' => 'Dzogchen',
            'vedanta' => 'Vedanta',
            'sufi' => 'Sufi',
            'yogic' => 'Yogic',
            'yoga' => 'Yogic',
            'mindfulness' => 'Mindfulness based stress reduction',
            'kabbal' => 'Kabbalistic',
            'daoist' => 'Daoist',
            'tao' => 'Daoist'
        ];
        
        foreach ($traditions as $keyword => $tradition) {
            if (strpos($text, $keyword) !== false) {
                return $tradition;
            }
        }
        
        return 'Mindfulness based stress reduction'; // Default
    }
    
    private function extractExperienceLevel(string $text): string
    {
        if (strpos($text, 'beginner') !== false || strpos($text, 'new') !== false || strpos($text, 'never') !== false || strpos($text, 'start') !== false) return 'Beginner';
        if (strpos($text, 'advanced') !== false || strpos($text, 'experienced') !== false || strpos($text, 'years') !== false) return 'Advanced';
        if (strpos($text, 'intermediate') !== false || strpos($text, 'some') !== false) return 'Intermediate';
        if (strpos($text, 'just start') !== false) return 'Just started';
        
        return 'Beginner'; // Default
    }
    
    private function extractChallenges(string $text): string
    {
        $challenges = [];
        
        if (strpos($text, 'focus') !== false || strpos($text, 'distract') !== false || strpos($text, 'mind wander') !== false) {
            $challenges[] = 'Difficulty focusing';
        }
        if (strpos($text, 'time') !== false || strpos($text, 'busy') !== false || strpos($text, 'schedule') !== false) {
            $challenges[] = 'Finding time';
        }
        if (strpos($text, 'critical') !== false || strpos($text, 'judge') !== false || strpos($text, 'hard on myself') !== false) {
            $challenges[] = 'Feeling self-critical';
        }
        if (strpos($text, 'uncomfort') !== false || strpos($text, 'pain') !== false || strpos($text, 'sit') !== false) {
            $challenges[] = 'Physical discomfort';
        }
        if (strpos($text, 'motivat') !== false || strpos($text, 'lazy') !== false || strpos($text, 'procrastinat') !== false) {
            $challenges[] = 'Low motivation';
        }
        
        return empty($challenges) ? 'Difficulty focusing' : implode(', ', $challenges);
    }
} 