<?php

namespace SentientCoach\Controllers;

use SentientCoach\Utils\Response;
use SentientCoach\Services\QuestionService;
use SentientCoach\Services\PlanService;

class HomeController
{
    private QuestionService $questionService;
    private PlanService $planService;
    
    public function __construct(QuestionService $questionService, PlanService $planService)
    {
        $this->questionService = $questionService;
        $this->planService = $planService;
    }
    
    public function index(): void
    {
        $content = $this->renderTemplate('landing', [
            'title' => 'Sentient AI Coach - Find Your Calm',
            'metaDescription' => 'Transform stress into serenity with a personalized meditation plan designed specifically for high-performers like you.'
        ]);
        
        Response::html($content);
    }
    
    public function questions(): void
    {
        $currentQuestion = $this->questionService->getCurrentQuestionNumber();
        
        // Only redirect to plan if questionnaire is complete, plan exists, AND user is not explicitly retaking
        // Check if this is a retake request (via URL parameter or session flag)
        $isRetaking = isset($_GET['retake']) || isset($_SESSION['retaking_questionnaire']);
        
        if ($this->questionService->isQuestionnaireComplete() && $this->planService->getCurrentPlan() && !$isRetaking) {
            Response::redirect('/plan');
            return;
        }
        
        // If user is retaking, reset the questionnaire but preserve the flag
        if ($isRetaking && $this->questionService->isQuestionnaireComplete()) {
            $this->questionService->resetQuestionnaire();
            $_SESSION['retaking_questionnaire'] = true;
            $currentQuestion = 1;
        }
        
        $content = $this->renderTemplate('questions', [
            'title' => 'Personalize Your Journey - Question ' . $currentQuestion . ' of 8',
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => 8
        ]);
        
        Response::html($content);
    }
    
    public function questionnaire(): void
    {
        $content = $this->renderTemplate('questionnaire', [
            'title' => 'Quick Questionnaire - 2 Minutes',
            'type' => 'questionnaire'
        ]);
        
        Response::html($content);
    }
    
    public function aiChat(): void
    {
        $content = $this->renderTemplate('ai-chat', [
            'title' => 'Chat with AI Coach',
            'type' => 'ai-chat'
        ]);
        
        Response::html($content);
    }
    
    public function plan(): void
    {
        $plan = $this->planService->getCurrentPlan();
        
        // Check if user completed questionnaire or AI chat
        // Consider questionnaire complete when Q8 has an answer (handles conditional Q7.5 correctly)
        $questionnaireComplete = isset($_SESSION['questionnaire_answers'][8]);
        $aiChatComplete = isset($_SESSION['ai_chat_conversation']) && count($_SESSION['ai_chat_conversation']) >= 10;
        $legacyComplete = $this->questionService->isQuestionnaireComplete();
        
        // DEBUG LOGGING FOR /plan ROUTE
        $debugFile = __DIR__ . '/../../logs/debug.log';
        file_put_contents($debugFile, "\n=== /plan entered ===" . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'plan_exists=' . ($plan ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'questionnaire_complete=' . ($questionnaireComplete ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'ai_chat_complete=' . ($aiChatComplete ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'legacy_complete=' . ($legacyComplete ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'answers_count=' . (isset($_SESSION['questionnaire_answers']) ? count($_SESSION['questionnaire_answers']) : 0) . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'answer_keys=' . (isset($_SESSION['questionnaire_answers']) ? implode(',', array_keys($_SESSION['questionnaire_answers'])) : 'none') . PHP_EOL, FILE_APPEND);
        
        // If no plan exists and no flow is complete, redirect to home
        if (!$plan && !$questionnaireComplete && !$aiChatComplete && !$legacyComplete) {
            file_put_contents($debugFile, 'redirect_reason=no_plan_and_not_complete' . PHP_EOL, FILE_APPEND);
            Response::redirect('/');
            return;
        }
        
        // If no plan exists but questionnaire is complete, stay here and show loading/generation state
        if (!$plan) {
            file_put_contents($debugFile, 'rendering=plan_generation_state' . PHP_EOL, FILE_APPEND);
            // The frontend will detect this and trigger plan generation
            $content = $this->renderTemplate('plan', [
                'title' => 'Generating Your Plan - Sentient AI Coach',
                'plan' => null
            ]);
            
            file_put_contents($debugFile, 'generation_template_content_length=' . strlen($content) . PHP_EOL, FILE_APPEND);
            Response::html($content);
            return;
        }
        
        file_put_contents($debugFile, 'rendering=plan_view' . PHP_EOL, FILE_APPEND);
        file_put_contents($debugFile, 'about_to_render_with_plan=yes' . PHP_EOL, FILE_APPEND);
        
        try {
            $content = $this->renderTemplate('plan', [
                'title' => $plan->title . ' - Your Personal Meditation Plan',
                'plan' => $plan
            ]);
            
            file_put_contents($debugFile, 'template_rendered_successfully=yes' . PHP_EOL, FILE_APPEND);
            file_put_contents($debugFile, 'template_content_length=' . strlen($content) . PHP_EOL, FILE_APPEND);
            Response::html($content);
        } catch (\Exception $e) {
            file_put_contents($debugFile, 'template_render_error=' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            throw $e;
        }
    }
    
    public function reset(): void
    {
        // Clear all session data
        $_SESSION = [];
        
        // Reset questionnaire
        $this->questionService->resetQuestionnaire();
        
        // Clear any existing plan
        unset($_SESSION["meditation_plan"]);
        unset($_SESSION["questionnaire_answers"]);
        unset($_SESSION["ai_chat_conversation"]);
        Response::redirect("/");
    }
    
    private function renderTemplate(string $template, array $data = []): string
    {
        $debugFile = __DIR__ . '/../../logs/debug.log';
        file_put_contents($debugFile, 'renderTemplate_called_with=' . $template . PHP_EOL, FILE_APPEND);
        
        // Extract variables for template
        extract($data);
        
        file_put_contents($debugFile, 'variables_extracted=yes' . PHP_EOL, FILE_APPEND);
        
        // Start output buffering
        ob_start();
        
        try {
            // Include the layout template
            include __DIR__ . '/../../templates/layout.php';
            
            // Get the content
            $content = ob_get_contents();
            ob_end_clean();
            
            file_put_contents($debugFile, 'template_included_successfully=yes' . PHP_EOL, FILE_APPEND);
            file_put_contents($debugFile, 'content_length=' . strlen($content) . PHP_EOL, FILE_APPEND);
            
            return $content;
        } catch (\Exception $e) {
            ob_end_clean();
            file_put_contents($debugFile, 'template_include_error=' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            throw $e;
        }
    }
} 