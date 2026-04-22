<?php

namespace SentientCoach\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class OpenAIService
{
    private Client $httpClient;
    private array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function generateQuestion(int $questionNumber, array $previousAnswers = []): array
    {
        $prompt = $this->buildQuestionPrompt($questionNumber, $previousAnswers);
        
        $response = $this->makeRequest([
            'model' => $this->config['models']['questions'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getQuestionSystemPrompt()
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $this->config['temperature']['questions'],
            'max_tokens' => 800,
            'response_format' => ['type' => 'json_object']
        ]);
        
        return json_decode($response['choices'][0]['message']['content'], true);
    }
    
    public function generatePlan(array $answers): array
    {
        $prompt = $this->buildPlanPrompt($answers);
        
        $response = $this->makeRequest([
            'model' => $this->config['models']['plans'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getPlanSystemPrompt()
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $this->config['temperature']['plans'],
            'max_tokens' => 2000,
            'response_format' => ['type' => 'json_object']
        ]);
        
        return json_decode($response['choices'][0]['message']['content'], true);
    }
    
    public function regeneratePlan(array $answers, string $feedback, array $originalPlan): array
    {
        $prompt = $this->buildRegeneratePlanPrompt($answers, $feedback, $originalPlan);
        
        $response = $this->makeRequest([
            'model' => $this->config['models']['plans'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getPlanSystemPrompt()
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $this->config['temperature']['plans'],
            'max_tokens' => 2000,
            'response_format' => ['type' => 'json_object']
        ]);
        
        return json_decode($response['choices'][0]['message']['content'], true);
    }
    
    public function chat(array $messages, float $temperature = 0.7, int $maxTokens = 1000): array
    {
        $response = $this->makeRequest([
            'model' => $this->config['models']['chat'] ?? $this->config['models']['plans'] ?? 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ]);
        
        return [
            'content' => $response['choices'][0]['message']['content'] ?? '',
            'role' => $response['choices'][0]['message']['role'] ?? 'assistant'
        ];
    }
    
    private function makeRequest(array $data): array
    {
        try {
            $response = $this->httpClient->post('chat/completions', [
                'json' => $data
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            error_log('OpenAI API Error: ' . $e->getMessage());
            throw new \Exception('AI service temporarily unavailable. Please try again.');
        }
    }
    
    private function getQuestionSystemPrompt(): string
    {
        return 'You are the Sentient AI Coach. Generate the next personalized meditation question efficiently.

## CORE TASK
Create ONE focused question with 3-5 clear options that builds on previous responses.

## QUESTION STRATEGY BY NUMBER
1. Primary goal/life situation
2. Available time and schedule  
3. Experience level and comfort
4. Specific challenges or obstacles
5. Practice preferences and style
6. Environment and lifestyle factors
7. Commitment level and expectations

## RESPONSE RULES
- Keep questions concise but warm
- Options should be practical and distinct
- Use simple, clear language
- Focus on actionable information for plan creation

## FORMAT
Return JSON with:
{
  "question": "One clear, empathetic question",
  "subtitle": "Brief context or encouragement",
  "options": [
    {
      "id": "option-id",
      "label": "Option Label",
      "description": "Brief description"
    }
  ]
}
Ensure each option is genuinely different and covers main user scenarios.';
    }
    
    private function getPlanSystemPrompt(): string
    {
        return 'You are the Sentient AI Coach. Create a personalized weekly meditation plan efficiently.

## YOUR EXPERTISE
20+ years meditation teaching, specializing in practical, sustainable programs for modern lifestyles.

## PLAN PRINCIPLES
1. **Personalized**: Reflect user\'s specific goals and constraints
2. **Progressive**: Build skills Monday through Sunday
3. **Practical**: Realistic for their lifestyle and experience
4. **Effective**: Practices that deliver real results

## WEEKLY STRUCTURE
- Mon: Foundation setting
- Tue-Wed: Core skill building  
- Thu: Mid-week integration
- Fri: Completion and transition
- Sat: Deeper practice (longer if appropriate)
- Sun: Reflection and preparation

## RESOURCE INTEGRATION (Updated for AI Use)

When delivering a daily plan, you must:

1. **Start with the Curated Resource List**  
   - Use the provided resource names/titles as the **authoritative list of approved resources**.  
   - Treat these names as "keywords" or "anchors" to search for live resources online.  

2. **Find Clickable Links Automatically**  
   - For each recommended resource:  
     - Search the internet for the most reliable, official, or widely trusted link.  
     - Prioritize:  
       1. Official websites (e.g., Healthy Minds app site, Insight Timer official app page).  
       2. Trusted video/audio platforms (official YouTube channels, podcasts).  
       3. Reputable organizations (UC Berkeley GGSC, Mayo Clinic, NHS, Headspace, etc.).  
   - Ensure the returned link is **working, safe, and direct** (not blogs or random reposts).  

3. **Guided vs. Self-Guided Logic**  
   - **Guided Days:** Match to curated entries like guided meditation apps, videos, or podcasts; provide official download or play links.  
   - **Self-Guided Days:** Match to timer apps, ambient music playlists, or written guides; provide direct app store or article/PDF links.  

4. **Technique-Specific Matching**  
   - Use the daily practice type to match curated keywords, then fetch links:  
     - *Breathing →* find "10-min guided breathing" video or PDF from curated list name.  
     - *Body Scan →* fetch official "Body Scan Meditation" video/audio guide.  
     - *Mindfulness →* fetch mindfulness practice from official app or science-backed article.  

5. **Experience-Level Fit**  
   - If beginner → prioritize beginner-friendly materials (clearly marked in resource title).  
   - If advanced → allow longer/advanced links (e.g., 30+ min sessions).  

6. **Response Formatting for the User**  
   - Show **resource title, why it\'s useful, and clickable link**.  
   - Example:  
     - **10-Min Guided Breathing – YouTube (Official Healthy Minds)**  
       Beginner-friendly practice to reduce stress.  
       [Watch here](https://www.youtube.com/officiallink)  

### How You Should Behave
- **Always anchor on your curated resource list.**  
  - Example: If the list says "Healthy Minds App," you should search "Healthy Minds App official download" and return the **App Store/Google Play official link**, not a random article.  
- **Fetch live, clickable links automatically.**  
- **Supplement with secondary credible sources** if the curated name can\'t be matched to a valid link.  
- **Explain briefly why the link is relevant** (duration, beginner-friendly, etc.).

## OUTPUT REQUIREMENTS
Return JSON with:
{
  "title": "Inspiring and personal title",
  "overview": "2-3 sentences about the journey",
  "schedule": [
    {
      "day": "Monday",
      "practice": "Practice Type",
      "duration": "X-Y min",
      "description": "What this practice does",
      "instructions": "Step-by-step guide",
      "coachingNotes": "Personal encouragement",
      "recommendedResources": [
        {
          "name": "Resource Name",
          "type": "App/YouTube/Website",
          "reason": "Why this resource fits this practice",
          "specificContent": "What to look for/search for",
          "link": "https://example.com/resource-link"
        }
      ]
    }
  ],
  "weeklyReflection": "Weekly guidance for progress",
  "successTips": ["Tip 1", "Tip 2", "Tip 3"]
}

Create a plan that feels personally designed for this user\'s unique situation and goals.';
    }
    
    private function buildQuestionPrompt(int $questionNumber, array $previousAnswers): string
    {
        $prompt = "Generate question #{$questionNumber} for the meditation questionnaire.\n\n";
        
        if (!empty($previousAnswers)) {
            $prompt .= "Previous answers:\n";
            foreach ($previousAnswers as $answer) {
                $prompt .= "Q{$answer['questionNumber']}: {$answer['question']}\n";
                $prompt .= "A: {$answer['answer']}\n\n";
            }
        }
        
        $prompt .= "Create a question that builds naturally on the previous responses and helps gather the information needed for question #{$questionNumber} according to the strategy.";
        
        return $prompt;
    }
    
    private function buildPlanPrompt(array $answers): string
    {
        $prompt = "Create a personalized 7-day meditation plan based on these user responses:\n\n";
        
        foreach ($answers as $answer) {
            $prompt .= "Q{$answer['questionNumber']}: {$answer['question']}\n";
            $prompt .= "A: {$answer['answer']}\n\n";
        }
        
        $prompt .= "Generate a comprehensive weekly meditation plan that addresses their specific needs, goals, and constraints.";
        
        return $prompt;
    }
    
    private function buildRegeneratePlanPrompt(array $answers, string $feedback, array $originalPlan): string
    {
        $prompt = "User feedback on their meditation plan: \"{$feedback}\"\n\n";
        $prompt .= "Original user responses:\n";
        
        foreach ($answers as $answer) {
            $prompt .= "Q{$answer['questionNumber']}: {$answer['question']}\n";
            $prompt .= "A: {$answer['answer']}\n\n";
        }
        
        $prompt .= "Please adjust the plan to address their feedback while maintaining the personalized approach based on their original responses.";
        
        return $prompt;
    }
} 