<?php

namespace SentientCoach\Services;

use SentientCoach\Models\MeditationPlan;
use SentientCoach\Models\MeditationDay;

class PlanService
{
    private OpenAIService $openAIService;
    private ResourceService $resourceService;
    
    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
        $this->resourceService = new ResourceService();
    }
    
    public function generatePlan(array $answers): MeditationPlan
    {
        try {
            $planData = $this->openAIService->generatePlan($answers);
            $plan = $this->createPlanFromData($planData, $answers);
            
            // Store in session
            $_SESSION['meditation_plan'] = $plan->toArray();
            
            return $plan;
        } catch (\Exception $e) {
            // Fallback to a basic plan if AI fails
            return $this->generateFallbackPlan($answers);
        }
    }
    
    public function regeneratePlan(array $answers, string $feedback): MeditationPlan
    {
        $originalPlan = $this->getCurrentPlan();
        
        try {
            $planData = $this->openAIService->regeneratePlan($answers, $feedback, $originalPlan->toArray());
            $plan = $this->createPlanFromData($planData, $answers);
            
            // Update stored plan
            $_SESSION['meditation_plan'] = $plan->toArray();
            
            return $plan;
        } catch (\Exception $e) {
            // Return current plan if regeneration fails
            return $originalPlan ?? $this->generateFallbackPlan($answers);
        }
    }
    
    public function getCurrentPlan(): ?MeditationPlan
    {
        if (!isset($_SESSION['meditation_plan'])) {
            return null;
        }
        
        return MeditationPlan::fromArray($_SESSION['meditation_plan']);
    }
    
    private function createPlanFromData(array $planData, array $answers): MeditationPlan
    {
        $schedule = [];
        foreach ($planData['schedule'] as $dayData) {
            $schedule[] = new MeditationDay(
                day: $dayData['day'],
                practice: $dayData['practice'],
                duration: $dayData['duration'],
                description: $dayData['description'],
                instructions: $dayData['instructions'],
                coachingNotes: $dayData['coachingNotes'],
                recommendedResources: $dayData['recommendedResources'] ?? []
            );
        }
        
        return new MeditationPlan(
            title: $planData['title'],
            overview: $planData['overview'],
            schedule: $schedule,
            weeklyReflection: $planData['weeklyReflection'],
            successTips: $planData['successTips'],
            trustedResources: [] // Remove plan-level resources since they're now in individual days
        );
    }
    
    private function getTrustedResourcesFromAnswers(array $answers): array
    {
        // Find Question 3 answer about meditation preference
        $preference = 'Both'; // Default to 'Both' if not found
        
        foreach ($answers as $answer) {
            // Check if this is question 3 (question number or content based)
            if ((isset($answer['questionNumber']) && $answer['questionNumber'] == 3) ||
                (isset($answer['question']) && strpos($answer['question'], 'Do you prefer') !== false)) {
                
                // Extract the preference from the answer
                if (isset($answer['answer'])) {
                    if (is_array($answer['answer'])) {
                        // Handle multi-select answer format (for questionnaire)
                        foreach ($answer['answer'] as $group) {
                            if (isset($group['Do you prefer:'])) {
                                $preference = $group['Do you prefer:'];
                                break;
                            }
                        }
                    } else {
                        // Handle single answer format or text containing preference
                        $answerText = $answer['answer'];
                        if (strpos($answerText, 'Guided meditation') !== false) {
                            $preference = 'Guided meditation';
                        } elseif (strpos($answerText, 'Self-guided meditation') !== false) {
                            $preference = 'Self-guided meditation';
                        } elseif (strpos($answerText, 'Both') !== false) {
                            $preference = 'Both';
                        }
                    }
                }
                break;
            }
        }
        
        return $this->resourceService->getResourcesForPreference($preference);
    }
    
    private function generateFallbackPlan(array $answers): MeditationPlan
    {
        $schedule = [
            new MeditationDay(
                day: 'Monday',
                practice: 'Foundation Breathing',
                duration: '5-8 min',
                description: 'Begin with simple breath awareness to establish your practice foundation.',
                instructions: 'Find a comfortable seated position. Close your eyes and focus on your natural breathing rhythm. When your mind wanders, gently return attention to your breath.',
                coachingNotes: 'Remember, it\'s perfectly normal for your mind to wander. The practice is in noticing and gently returning to your breath.',
                recommendedResources: [
                    [
                        'name' => 'Insight Timer',
                        'type' => 'App',
                        'reason' => 'Perfect for beginner breathing meditations with guided sessions',
                        'specificContent' => 'Search for "beginner breath awareness" or "foundation breathing"',
                        'link' => 'https://insighttimer.com/'
                    ]
                ]
            ),
            new MeditationDay(
                day: 'Tuesday',
                practice: 'Body Awareness',
                duration: '7-10 min',
                description: 'Develop awareness of physical sensations and release tension.',
                instructions: 'Starting from your toes, slowly scan your body upward. Notice any tension or sensations without trying to change them.',
                coachingNotes: 'If you find areas of tension, simply acknowledge them with kindness. The goal is awareness, not perfection.',
                recommendedResources: [
                    [
                        'name' => 'UCLA MARC',
                        'type' => 'Website',
                        'reason' => 'Research-backed body scan meditations for developing physical awareness',
                        'specificContent' => 'Try their "Body Scan for Sleep" or "Complete Body Scan Meditation"',
                        'link' => 'https://www.uclahealth.org/programs/marc/free-guided-meditations'
                    ]
                ]
            ),
            new MeditationDay(
                day: 'Wednesday',
                practice: 'Mindful Observation',
                duration: '8-12 min',
                description: 'Practice observing thoughts and emotions without judgment.',
                instructions: 'Sit quietly and notice whatever arises in your mind. Observe thoughts like clouds passing in the sky - present but not permanent.',
                coachingNotes: 'You\'re not trying to stop thoughts, just developing a different relationship with them.',
                recommendedResources: [
                    [
                        'name' => 'Tara Brach YouTube',
                        'type' => 'YouTube',
                        'reason' => 'Excellent guided mindfulness practices for observing thoughts with compassion',
                        'specificContent' => 'Search for "RAIN meditation" or "mindful awareness of thoughts"',
                        'link' => 'https://www.youtube.com/@tarabrach'
                    ]
                ]
            ),
            new MeditationDay(
                day: 'Thursday',
                practice: 'Stress Relief Breathing',
                duration: '6-10 min',
                description: 'Use breath techniques specifically designed to reduce stress and anxiety.',
                instructions: 'Breathe in for 4 counts, hold for 4, exhale for 6. Repeat this cycle, focusing on the longer exhale to activate relaxation.',
                coachingNotes: 'The extended exhale helps activate your body\'s natural relaxation response. Take your time.',
                recommendedResources: [
                    [
                        'name' => 'Healthy Minds Program',
                        'type' => 'App',
                        'reason' => 'Science-based breathing exercises specifically for stress and anxiety relief',
                        'specificContent' => 'Look for their "Stress and Difficulty" section with breathing practices',
                        'link' => 'https://hminnovations.org/meditation-app'
                    ]
                ]
            ),
            new MeditationDay(
                day: 'Friday',
                practice: 'Gratitude & Reflection',
                duration: '8-12 min',
                description: 'End the work week with appreciation and positive reflection.',
                instructions: 'Begin with a few minutes of breathing, then bring to mind three things you\'re grateful for this week. Feel the appreciation in your body.',
                coachingNotes: 'Gratitude practice literally rewires your brain for positivity. Let yourself really feel the appreciation.',
                recommendedResources: [
                    [
                        'name' => 'Oak Meditation',
                        'type' => 'App',
                        'reason' => 'Simple timer with optional gratitude prompts to support your reflection practice',
                        'specificContent' => 'Use the unguided timer or try their gratitude-focused sessions',
                        'link' => 'https://www.oakmeditation.com/'
                    ]
                ]
            ),
            new MeditationDay(
                day: 'Saturday',
                practice: 'Extended Mindfulness',
                duration: '15-20 min',
                description: 'Deepen your practice with a longer session combining multiple techniques.',
                instructions: 'Start with breath awareness (5 min), move to body scanning (5 min), then open awareness of thoughts and sensations (5-10 min).',
                coachingNotes: 'This longer session helps integrate all the skills you\'ve been developing. Be patient with yourself.',
                recommendedResources: [
                    [
                        'name' => 'Smiling Mind',
                        'type' => 'App',
                        'reason' => 'Excellent longer-form mindfulness sessions that combine multiple techniques',
                        'specificContent' => 'Try their "Adult" programs for comprehensive mindfulness practice',
                        'link' => 'https://www.smilingmind.com.au/'
                    ]
                ]
            ),
            new MeditationDay(
                day: 'Sunday',
                practice: 'Intention Setting',
                duration: '10-15 min',
                description: 'Prepare for the coming week with clarity and purposeful intention.',
                instructions: 'After centering yourself with breathing, reflect on how you want to show up in the coming week. Set a clear, kind intention.',
                coachingNotes: 'This practice helps bridge your meditation into daily life. Your intention can be simple but meaningful.',
                recommendedResources: [
                    [
                        'name' => 'Jack Kornfield Website',
                        'type' => 'Website',
                        'reason' => 'Guided intention-setting meditations and wisdom for integrating practice into life',
                        'specificContent' => 'Look for "Loving-Kindness" and "Setting Intentions" guided practices',
                        'link' => 'https://jackkornfield.com/meditation-instructions/'
                    ]
                ]
            )
        ];
        
        return new MeditationPlan(
            title: 'Your Personal Meditation Journey',
            overview: 'A balanced 7-day program designed to introduce you to core meditation practices while building sustainable habits that fit your lifestyle.',
            schedule: $schedule,
            weeklyReflection: 'At the end of this week, reflect on which practices felt most natural and beneficial. Notice any changes in your stress levels, sleep quality, or overall sense of calm. Remember, meditation is a practice - be kind to yourself as you develop this new skill.',
            successTips: [
                'Practice at the same time each day to build consistency',
                'Start with shorter sessions and gradually increase duration',
                'Be patient with yourself - meditation is a skill that develops over time',
                'Focus on consistency over perfection',
                'Create a dedicated quiet space for your practice'
            ],
            trustedResources: [] // Resources are now embedded in individual days
        );
    }
} 