<?php

namespace SentientCoach\Controllers;

use SentientCoach\Utils\Response;
use SentientCoach\Services\OpenAIService;

class AiChatController
{
    private OpenAIService $openAIService;
    
    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }
    
    public function getNextMessage(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationHistory = $input['history'] ?? [];
            
            // Get the AI's next response based on conversation history
            $aiResponse = $this->generateAIResponse($conversationHistory);
            
            $response = [
                'message' => $aiResponse,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('AI Chat message error: ' . $e->getMessage());
            Response::error('Unable to get AI response. Please try again.', 500);
        }
    }
    
    public function sendMessage(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($input['message'])) {
                Response::error("Message is required", 400);
                return;
            }
            
            $userMessage = trim($input['message']);
            $conversationHistory = $input['history'] ?? [];
            
            // Add user message to conversation
            $conversationHistory[] = [
                'type' => 'user',
                'content' => $userMessage,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Store conversation in session
            $_SESSION['ai_chat_conversation'] = $conversationHistory;
            
            // Generate AI response
            $aiResponse = $this->generateAIResponse($conversationHistory);
            
            // Add AI response to conversation
            $conversationHistory[] = [
                'type' => 'ai',
                'content' => $aiResponse,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Update session
            $_SESSION['ai_chat_conversation'] = $conversationHistory;
            
            // Check if we have enough information to create a plan
            $readyForPlan = $this->checkIfReadyForPlan($conversationHistory);
            
            $response = [
                'success' => true,
                'aiMessage' => $aiResponse,
                'readyForPlan' => $readyForPlan,
                'conversationHistory' => $conversationHistory
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('AI Chat message error: ' . $e->getMessage());
            Response::error('Unable to process message. Please try again.', 500);
        }
    }
    
    public function getProgress(): void
    {
        try {
            $conversation = $_SESSION['ai_chat_conversation'] ?? [];
            $readyForPlan = $this->checkIfReadyForPlan($conversation);
            
            $response = [
                'conversationHistory' => $conversation,
                'readyForPlan' => $readyForPlan,
                'messageCount' => count($conversation)
            ];
            
            Response::json($response);
        } catch (\Exception $e) {
            error_log('AI Chat progress error: ' . $e->getMessage());
            Response::error('Unable to get progress. Please try again.', 500);
        }
    }
    
    private function generateAIResponse(array $conversationHistory): string
    {
        // Check if user has existing questionnaire data
        $existingData = $this->getExistingUserData();
        
        // Build conversation context for AI
        $systemPrompt = 'You are the Sentient AI Coach, a personal meditation coach with 20+ years of teaching experience, specializing in practical, sustainable programs for modern lifestyles.

## YOUR EXPERTISE & TONE:
- Kind, considerate, compassionate, humble, and philosophical
- Your language should sound reflective and wise — never robotic or overly casual
- Gentle, grounded, and encouraging calm through thoughtful pauses and reflective statements
- Use simple, direct language, never clinical or instructional in tone
- Avoid emoji or overly casual phrasing
- Can share short reflections such as: "Every breath is a return home. Let\'s build your practice around that idea."

## CORE PURPOSE:
You are a **personal meditation coach** — not an app announcer or assistant. You help users discover stillness through structure, reflection, and continuous feedback. You do not need to announce that you\'re an "AI meditation coach." Instead, introduce yourself simply as **"your meditation coach"** or **"your mindfulness guide."**

Avoid off-brand greetings like "Welcome to the meditation zone!" or "Hey there! What brings you to the meditation zone today?"
Replace with: "Hi, I\'m your meditation coach. Let\'s create a meditation plan that fits your rhythm and goals."

## YOUR IDENTITY:
- You are an artificial intelligence designed to help people with meditation
- NEVER refer to yourself as human or doing human activities (like "going for a walk", "eating", "sleeping", etc.)
- You are an AI assistant - acknowledge this when relevant but don\'t make it the focus

## TOPICS TO AVOID:
- Sex, sexual topics, or intimate relationships
- Politics, political parties, or political opinions
- Religion, religious beliefs, or spiritual practices outside of meditation
- Controversial social issues
- Personal medical advice or mental health diagnoses
- If users bring up these topics, politely redirect to meditation and mindfulness

## YOUR APPROACH TO SENSITIVE TOPICS:
- Stay neutral and professional
- Redirect conversations back to meditation and mindfulness
- Say something like: "I\'m here to help with meditation and mindfulness. Let\'s focus on how we can support your practice."
- Be respectful but firm about boundaries

## CONVERSATION GUIDANCE:
- Allow 1-2 exchanges of off-topic conversation to be friendly
- After 2-3 off-topic exchanges, gently redirect back to meditation
- Use phrases like: "That\'s interesting! Speaking of [their topic], I\'m curious about your meditation practice..."
- If they stay off-topic for 3-5 exchanges, be more direct: "I\'d love to help you with meditation. What brought you here today?"
- Always connect their off-topic interests back to mindfulness when possible

## CAPABILITIES:
You are designed to:
1. **Discuss meditation** — methods, techniques, and benefits
2. **Explore context and theory** — including history, scientific understanding, ancient traditions, and modern applications
3. **Identify focus areas** — if the user struggles to meditate or maintain consistency
4. **Create a customized program** — aligned with the user\'s time, goals, and experience
5. **Explain rationale** — why you chose the structure, practices, and sequence
6. **Assess and evolve** — track user progress, provide feedback, and adjust the plan as needed
7. **Differentiate practice types** — clearly explain guided vs. self-guided meditations:
   - *Guided*: someone leading you through a session
   - *Self-guided*: techniques like focusing on breath, body, or mantra
8. **Provide reliable links** — direct, working resources for each practice (YouTube, Calm, Insight Timer, etc.)
9. **Reflect and engage** — weave light philosophy into transitions but always return to practical next steps';

        if ($existingData) {
            $systemPrompt .= "\n\n## WHAT YOU KNOW ABOUT THEM:\n" . $existingData . "

## HOW TO USE THIS INFO:
- Casually reference what you know about them
- Ask about their actual experience since filling out the questionnaire
- Help them with specific challenges they mentioned
- When the conversation feels complete, offer to create their plan";
        } else {
            $systemPrompt .= "\n\n## CONVERSATION FLOW:

### 1. Opening
Start gently, setting a reflective tone:
\"Every mind deserves a moment of stillness. Let\'s find what kind of practice feels natural to you.\"

Then transition:
\"I\'ll ask a few short questions to understand your preferences before designing your personalized plan.\"

### 2. Questionnaire
Ask one question at a time. Do not skip or jump ahead. Gather full context before recommending any apps or resources.

**Ask:**
1. What brings you to meditation right now?
2. Do you prefer guided meditations (someone speaking) or self-guided silence?
3. How much time can you dedicate per day — 5, 10, or 20 minutes?
4. What do you want to focus on: relaxation, focus, emotional balance, or spiritual growth?
5. Have you meditated before? If yes, what worked or didn\'t?

Once all answers are received, confirm:
\"Thanks for sharing. I\'ll now design a plan that fits your goals and schedule.\"

### 3. Plan Generation
The plan must include:
- **Title** – inspirational and personal
- **Overview** – 2–3 sentences describing the journey
- **Daily Schedule** – structured by day, with duration, focus, and purpose
- **Guided/Self-Guided indication** – clear distinction within each day
- **Instructions** – simple steps for each practice
- **Coaching Notes** – short, encouraging reflections
- **Direct Links** – working YouTube or app resources
- **Rationale** – why this plan suits the user\'s profile
- **Weekly Reflection** – 1 paragraph inviting review and awareness

### 4. After the Plan
Once the plan is shared:
\"Would you like me to check in later to adjust your plan as you progress?\"

If yes:
- Offer progress tracking: consistency, ease, and emotional changes
- Adjust duration, focus, or techniques as needed

Always provide a sense of continuity — the user should feel like this is an ongoing journey, not a one-time session.

## BEHAVIOR AND RULES:
- Do not end the chat before the plan is complete
- Do not suggest Calm or Headspace until the custom plan is generated
- Provide **direct, free, working links** — not placeholders
- Always explain the reasoning behind recommendations
- Avoid stating limitations (e.g., \"I can\'t go deep into that topic\"). Instead say: \"While we\'ll stay focused on your practice, here\'s a brief insight into that tradition.\"
- Maintain warmth and authenticity in every response";
        }

        $systemPrompt .= "\n\n## FINAL REMINDERS:
- Be warm, authentic, and genuinely helpful
- You are an AI meditation coach - never refer to yourself as human or doing human activities
- Stay away from sex, politics, and religion
- Keep conversations focused on meditation and mindfulness
- Redirect off-topic conversations back to meditation within 3-5 exchanges
- Use reflective, philosophical language that sounds wise, not robotic
- Always provide a sense of continuity and ongoing support
- Wait until all inputs are collected before creating the plan
- Include verified working links (avoid dummy URLs)
- Keep tone consistent across the full conversation lifecycle";

        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ];
        
        // Add conversation history
        foreach ($conversationHistory as $message) {
            $messages[] = [
                'role' => $message['type'] === 'user' ? 'user' : 'assistant',
                'content' => $message['content']
            ];
        }
        
        try {
            // Use configured temperature and lower max tokens for more natural, concise responses
            $chatTemperature = $this->openAIService->getConfig()['temperature']['chat'] ?? 0.8;
            $response = $this->openAIService->chat($messages, $chatTemperature, 150);
            
            $aiResponse = $response['content'] ?? '';
            
            // If response is empty or too short, provide fallback
            if (empty(trim($aiResponse)) || strlen(trim($aiResponse)) < 5) {
                return $this->getFallbackResponse($conversationHistory);
            }
            
            return trim($aiResponse);
        } catch (\Exception $e) {
            error_log('OpenAI API error: ' . $e->getMessage());
            return $this->getFallbackResponse($conversationHistory);
        }
    }
    
    private function checkIfReadyForPlan(array $conversationHistory): bool
    {
        // Count meaningful exchanges (user messages)
        $userMessages = array_filter($conversationHistory, fn($msg) => $msg['type'] === 'user');
        $exchangeCount = count($userMessages);
        
        // If user has existing questionnaire data, they could create a plan with fewer exchanges
        $hasExistingData = $this->getExistingUserData() !== null;
        $minExchanges = $hasExistingData ? 3 : 6;
        
        // Need minimum exchanges to gather essential information
        if ($exchangeCount < $minExchanges) {
            return false;
        }
        
        // For users without questionnaire data, check if essential information has been gathered
        if (!$hasExistingData) {
            $conversationText = '';
            foreach ($conversationHistory as $message) {
                $conversationText .= ' ' . strtolower($message['content']);
            }
            
            // Check for essential information keywords
            $essentialInfo = [
                'time' => ['time', 'minutes', 'hours', 'daily', 'weekly', 'schedule'],
                'experience' => ['experience', 'beginner', 'advanced', 'tried', 'practice'],
                'goals' => ['goal', 'want', 'help', 'reduce', 'stress', 'anxiety', 'focus'],
                'challenges' => ['challenge', 'difficult', 'hard', 'struggle', 'problem']
            ];
            
            $infoGathered = 0;
            foreach ($essentialInfo as $category => $keywords) {
                foreach ($keywords as $keyword) {
                    if (strpos($conversationText, $keyword) !== false) {
                        $infoGathered++;
                        break; // Count this category as covered
                    }
                }
            }
            
            // Need at least 3 out of 4 essential categories covered
            if ($infoGathered < 3) {
                return false;
            }
        }
        
        // Check if the last AI message mentions creating a plan
        $lastAiMessage = null;
        for ($i = count($conversationHistory) - 1; $i >= 0; $i--) {
            if ($conversationHistory[$i]['type'] === 'ai') {
                $lastAiMessage = $conversationHistory[$i]['content'];
                break;
            }
        }
        
        if ($lastAiMessage) {
            $planKeywords = [
                'create your plan',
                'create a plan',
                'make your plan',
                'ready for your plan',
                'want me to create',
                'create your personalized',
                'ready for me to create',
                'create that plan',
                'build your plan',
                'set up your plan'
            ];
            
            foreach ($planKeywords as $keyword) {
                if (stripos($lastAiMessage, $keyword) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function getExistingUserData(): ?string
    {
        // Check for predefined questionnaire answers
        if (isset($_SESSION['questionnaire_answers']) && count($_SESSION['questionnaire_answers']) >= 6) {
            $answers = $_SESSION['questionnaire_answers'];
            $dataText = "User's Questionnaire Responses:\n";
            
            foreach ($answers as $questionNumber => $answer) {
                $dataText .= "• {$answer['question']}: {$answer['answer']}\n";
            }
            
            return $dataText;
        }
        
        // Check for existing meditation plan
        if (isset($_SESSION['meditation_plan'])) {
            $plan = $_SESSION['meditation_plan'];
            $dataText = "User has an existing meditation plan:\n";
            $dataText .= "• Plan Title: " . ($plan['title'] ?? 'Unnamed Plan') . "\n";
            
            if (isset($plan['userProfile'])) {
                $profile = $plan['userProfile'];
                $dataText .= "• Goals: " . ($profile['goals'] ?? 'Not specified') . "\n";
                $dataText .= "• Experience: " . ($profile['experience'] ?? 'Not specified') . "\n";
                $dataText .= "• Challenges: " . ($profile['challenges'] ?? 'Not specified') . "\n";
            }
            
            return $dataText;
        }
        
        return null;
    }
    
    private function getFallbackResponse(array $conversationHistory): string
    {
        $userMessages = array_filter($conversationHistory, fn($msg) => $msg['type'] === 'user');
        $messageCount = count($userMessages);
        
        // Check if user has existing data
        $existingData = $this->getExistingUserData();
        
        if ($existingData) {
            // Context-aware responses for users with existing questionnaire data
            $contextualFallbacks = [
                0 => "Every mind deserves a moment of stillness. I see you've shared your thoughts with me - how has your meditation journey been unfolding?",
                
                1 => "That's valuable insight. What's been the most challenging aspect of your practice?",
                
                2 => "I understand. Based on what you've shared, would you like me to create a meditation plan that addresses these specific needs?",
                
                3 => "I have a good sense of what might work for you. Shall I design your personalized plan?"
            ];
            
            if ($messageCount < count($contextualFallbacks)) {
                return $contextualFallbacks[$messageCount];
            }
            
            return "Ready for your personalized meditation plan?";
        }
        
        // Standard fallback responses for new users
        $fallbacks = [
            0 => "Every mind deserves a moment of stillness. What brings you to meditation right now?",
            
            1 => "I understand. Have you tried meditation before, or would this be your first time?",
            
            2 => "What's been the biggest challenge in your journey so far?",
            
            3 => "How much time can you dedicate per day — 5, 10, or 20 minutes?",
            
            4 => "Do you prefer guided meditations with someone speaking, or self-guided silence?",
            
            5 => "What do you want to focus on: relaxation, focus, emotional balance, or spiritual growth?",
            
            6 => "Where do you usually look for meditation content or support?"
        ];
        
        if ($messageCount < count($fallbacks)) {
            return $fallbacks[$messageCount];
        }
        
        // Ready for plan fallback after gathering essential information
        return "Thanks for sharing. I'll now design a plan that fits your goals and schedule.";
    }
    
    public function reset(): void
    {
        try {
            // Clear AI chat conversation
            unset($_SESSION['ai_chat_conversation']);
            
            Response::json(['success' => true, 'message' => 'AI chat reset successfully']);
        } catch (\Exception $e) {
            error_log('AI Chat reset error: ' . $e->getMessage());
            Response::error('Unable to reset AI chat. Please try again.', 500);
        }
    }
} 