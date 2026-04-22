<?php

namespace SentientCoach\Controllers;

use SentientCoach\Utils\Response;
use SentientCoach\Services\QuestionService;

class QuestionController
{
    private QuestionService $questionService;
    
    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }
    
    public function generateQuestion(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $questionNumber = $input['questionNumber'] ?? 1;
            
            // Get previous answers for context
            $previousAnswers = [];
            if ($questionNumber > 1) {
                $userAnswers = $this->questionService->getUserAnswers();
                foreach ($userAnswers as $answer) {
                    if ($answer['questionNumber'] < $questionNumber) {
                        $previousAnswers[] = $answer;
                    }
                }
            }
            
            $questionData = $this->questionService->getNextQuestion($questionNumber, $previousAnswers);
            
            Response::json($questionData);
        } catch (\Exception $e) {
            error_log('Question generation error: ' . $e->getMessage());
            Response::error('Unable to generate question. Please try again.', 500);
        }
    }
    
    public function submitAnswer(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            $requiredFields = ['questionNumber', 'question', 'answer'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    Response::error("Missing required field: {$field}", 400);
                    return;
                }
            }
            
            $questionNumber = (int)$input['questionNumber'];
            $question = trim($input['question']);
            $answer = trim($input['answer']);
            $isCustom = $input['isCustom'] ?? false;
            $optionId = $input['optionId'] ?? null;
            
            // Validate question number
            if ($questionNumber < 1 || $questionNumber > 7) {
                Response::error('Invalid question number', 400);
                return;
            }
            
            // Save the answer
            $answerObj = $this->questionService->saveAnswer(
                $questionNumber,
                $question,
                $answer,
                $isCustom,
                $optionId
            );
            
            // Check if questionnaire is now complete
            $isComplete = $this->questionService->isQuestionnaireComplete();
            
            // If questionnaire is complete and this was a retake, clear the retaking flag
            if ($isComplete && isset($_SESSION['retaking_questionnaire'])) {
                unset($_SESSION['retaking_questionnaire']);
            }
            
            // Prepare response
            $response = [
                'success' => true,
                'answer' => $answerObj->toArray(),
                'nextQuestion' => $questionNumber + 1,
                'isComplete' => $isComplete
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('Answer submission error: ' . $e->getMessage());
            Response::error('Unable to save answer. Please try again.', 500);
        }
    }
    
    public function getProgress(): void
    {
        try {
            $userAnswers = $this->questionService->getUserAnswers();
            $currentQuestion = $this->questionService->getCurrentQuestionNumber();
            $isComplete = $this->questionService->isQuestionnaireComplete();
            
            $response = [
                'currentQuestion' => $currentQuestion,
                'totalQuestions' => 8,
                'progress' => round((count($userAnswers) / 8) * 100),
                'answersCount' => count($userAnswers),
                'isComplete' => $isComplete,
                'answers' => $userAnswers
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('Progress check error: ' . $e->getMessage());
            Response::error('Unable to get progress. Please try again.', 500);
        }
    }
} 