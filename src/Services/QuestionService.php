<?php

namespace SentientCoach\Services;

use SentientCoach\Models\Answer;

class QuestionService
{
    private OpenAIService $openAIService;
    
    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }
    
    public function getNextQuestion(int $questionNumber, array $previousAnswers = []): array
    {
        // Validate question number
        if ($questionNumber < 1 || $questionNumber > 8) {
            throw new \InvalidArgumentException('Question number must be between 1 and 8');
        }
        
        try {
            $questionData = $this->openAIService->generateQuestion($questionNumber, $previousAnswers);
            
            // Add question metadata
            $questionData['questionNumber'] = $questionNumber;
            $questionData['totalQuestions'] = 8;
            $questionData['progress'] = round(($questionNumber - 1) / 8 * 100);
            
            return $questionData;
        } catch (\Exception $e) {
            // Fallback to static questions if AI fails
            return $this->getFallbackQuestion($questionNumber);
        }
    }
    
    public function saveAnswer(int $questionNumber, string $question, string $answer, bool $isCustom, ?string $optionId = null): Answer
    {
        $answerObj = new Answer(
            questionNumber: $questionNumber,
            question: $question,
            answer: $answer,
            isCustom: $isCustom,
            optionId: $optionId
        );
        
        // Store in session
        if (!isset($_SESSION['user_answers'])) {
            $_SESSION['user_answers'] = [];
        }
        
        $_SESSION['user_answers'][$questionNumber] = $answerObj->toArray();
        $_SESSION['current_question'] = $questionNumber + 1;
        
        return $answerObj;
    }
    
    public function getUserAnswers(): array
    {
        return $_SESSION['user_answers'] ?? [];
    }
    
    public function getCurrentQuestionNumber(): int
    {
        return $_SESSION['current_question'] ?? 1;
    }
    
    public function isQuestionnaireComplete(): bool
    {
        return $this->getCurrentQuestionNumber() > 8;
    }
    
    public function resetQuestionnaire(): void
    {
        unset($_SESSION['user_answers']);
        unset($_SESSION['current_question']);
        unset($_SESSION['meditation_plan']);
    }
    
    private function getFallbackQuestion(int $questionNumber): array
    {
        $fallbackQuestions = [
            1 => [
                'question' => 'What is your primary motivation for starting meditation?',
                'subtitle' => 'Understanding your goals helps create the perfect plan',
                'options' => [
                    ['id' => 'stress-relief', 'label' => 'Stress Relief', 'description' => 'Manage daily stress and find calm'],
                    ['id' => 'focus', 'label' => 'Better Focus', 'description' => 'Improve concentration and clarity'],
                    ['id' => 'sleep', 'label' => 'Better Sleep', 'description' => 'Improve sleep quality and relaxation'],
                    ['id' => 'anxiety', 'label' => 'Reduce Anxiety', 'description' => 'Find peace and emotional balance'],
                    ['id' => 'growth', 'label' => 'Personal Growth', 'description' => 'Develop mindfulness and self-awareness']
                ]
            ],
            2 => [
                'question' => 'How much time can you realistically dedicate to meditation each day?',
                'subtitle' => 'We\'ll create a plan that fits your schedule',
                'options' => [
                    ['id' => '5-min', 'label' => '5-8 minutes', 'description' => 'Perfect for busy schedules'],
                    ['id' => '10-min', 'label' => '10-15 minutes', 'description' => 'Good balance of time and depth'],
                    ['id' => '20-min', 'label' => '20+ minutes', 'description' => 'Deep practice sessions'],
                    ['id' => 'varies', 'label' => 'It varies', 'description' => 'Different amounts on different days']
                ]
            ],
            3 => [
                'question' => 'What\'s your experience level with meditation?',
                'subtitle' => 'We\'ll match practices to your comfort level',
                'options' => [
                    ['id' => 'beginner', 'label' => 'Complete Beginner', 'description' => 'Never meditated before'],
                    ['id' => 'some', 'label' => 'Some Experience', 'description' => 'Tried it a few times'],
                    ['id' => 'regular', 'label' => 'Regular Practice', 'description' => 'Meditate occasionally'],
                    ['id' => 'experienced', 'label' => 'Very Experienced', 'description' => 'Daily practice for months/years']
                ]
            ],
            4 => [
                'question' => 'What\'s your biggest challenge when it comes to meditation?',
                'subtitle' => 'Knowing your obstacles helps us address them',
                'options' => [
                    ['id' => 'time', 'label' => 'Finding Time', 'description' => 'Busy schedule makes it hard'],
                    ['id' => 'focus', 'label' => 'Staying Focused', 'description' => 'Mind wanders too much'],
                    ['id' => 'consistency', 'label' => 'Being Consistent', 'description' => 'Hard to make it a habit'],
                    ['id' => 'technique', 'label' => 'Knowing What to Do', 'description' => 'Unsure about proper techniques']
                ]
            ],
            5 => [
                'question' => 'What type of meditation practices appeal to you most?',
                'subtitle' => 'We\'ll focus on styles that resonate with you',
                'options' => [
                    ['id' => 'breathing', 'label' => 'Breathing Exercises', 'description' => 'Focus on breath patterns'],
                    ['id' => 'body-scan', 'label' => 'Body Awareness', 'description' => 'Physical sensations and relaxation'],
                    ['id' => 'mindfulness', 'label' => 'Present Moment', 'description' => 'Awareness of thoughts and surroundings'],
                    ['id' => 'guided', 'label' => 'Guided Imagery', 'description' => 'Visualization and mental journeys']
                ]
            ],
            6 => [
                'question' => 'When and where do you prefer to meditate?',
                'subtitle' => 'We\'ll tailor the plan to your environment',
                'options' => [
                    ['id' => 'morning', 'label' => 'Morning at Home', 'description' => 'Start the day mindfully'],
                    ['id' => 'evening', 'label' => 'Evening Wind-down', 'description' => 'Relax before bed'],
                    ['id' => 'work-break', 'label' => 'Work Breaks', 'description' => 'Quick sessions during the day'],
                    ['id' => 'flexible', 'label' => 'Flexible Timing', 'description' => 'Whenever I can fit it in']
                ]
            ],
            7 => [
                'question' => 'What level of commitment are you ready to make?',
                'subtitle' => 'Being honest helps create a sustainable plan',
                'options' => [
                    ['id' => 'gentle', 'label' => 'Gentle Start', 'description' => 'Easy introduction to build confidence'],
                    ['id' => 'steady', 'label' => 'Steady Practice', 'description' => 'Consistent daily sessions'],
                    ['id' => 'intensive', 'label' => 'Intensive Focus', 'description' => 'Ready for a serious commitment'],
                    ['id' => 'experimental', 'label' => 'Just Exploring', 'description' => 'Trying it out to see if it helps']
                ]
            ]
        ];
        
        $questionData = $fallbackQuestions[$questionNumber] ?? $fallbackQuestions[1];
        $questionData['questionNumber'] = $questionNumber;
        $questionData['totalQuestions'] = 8;
        $questionData['progress'] = round(($questionNumber - 1) / 8 * 100);
        
        return $questionData;
    }
} 