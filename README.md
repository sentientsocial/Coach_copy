# 🧘‍♀️ Sentient AI Coach - PHP Meditation App

A complete PHP-based AI meditation coaching system that generates personalized weekly meditation plans through intelligent conversational interfaces. Features both web interface and comprehensive API for mobile app integration.

## ✨ Features

### Core Functionality
- **AI-Personalized Plans**: GPT-5 powered algorithms analyze user responses to create tailored meditation programs
- **Dual Interface**: Web-based questionnaire wizard + AI chat interface
- **7-Question Dynamic Flow**: Adaptive questioning system with conditional logic (Q6.5 for experienced users)
- **AI Chat Coach**: Conversational meditation guidance with philosophical, reflective responses
- **Multiple Export Formats**: Calendar (.ics), HTML, Text, and JSON exports
- **Plan Customization**: Feedback system for regenerating plans based on user input
- **Dark/Light Theme**: Persistent theme switching with system preference detection
- **Responsive Design**: Mobile-first approach with beautiful Tailwind CSS styling

### Technical Features
- **PHP 8.1+** with modern features (typed properties, enums, match expressions)
- **OpenAI GPT-5** integration for all AI functions (questions, plans, chat)
- **Session-based** user management (no database required)
- **Fallback Systems** for AI failures
- **Error Handling** with retry logic
- **Security Features** (CSRF protection, input validation, rate limiting)
- **RESTful API** for mobile app integration

## 🚀 Quick Start

### Prerequisites
- PHP 8.1 or higher
- Composer
- OpenAI API access

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ai-coach-php
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure OpenAI API**
   - The OpenAI API key is already configured in `config/app.php`
   - For production, use environment variables or update the config file

4. **Start the development server**
   ```bash
   cd public
   php -S localhost:8000
   ```

5. **Open your browser**
   Navigate to `http://localhost:8000`

## 📁 Project Structure

```
ai-coach-php/
├── public/
│   ├── index.php              # Main entry point & routing
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css        # Custom CSS styles
│   │   └── js/
│   │       └── app.js         # Frontend JavaScript
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php     # Landing page & reset
│   │   ├── QuestionnaireController.php  # Questionnaire flow
│   │   ├── AiChatController.php   # AI chat interface
│   │   ├── PlanController.php     # Plan generation & export
│   │   └── QuestionController.php # Legacy question controller
│   ├── Services/
│   │   ├── OpenAIService.php      # GPT-5 AI integration
│   │   ├── QuestionService.php    # Question management
│   │   ├── PlanService.php        # Plan generation logic
│   │   └── ResourceService.php    # Resource recommendations
│   ├── Models/
│   │   ├── Answer.php             # Answer data model
│   │   ├── MeditationPlan.php     # Plan data model
│   │   └── MeditationDay.php      # Daily practice model
│   └── Utils/
│       ├── Router.php             # URL routing
│       ├── Response.php           # API response handling
│       └── ExportService.php      # Plan export functionality
├── templates/
│   ├── layout.php             # Base layout template
│   ├── landing.php            # Homepage
│   ├── questions.php          # Question flow interface
│   ├── ai-chat.php            # AI chat interface
│   └── plan.php              # Plan display & export
├── config/
│   └── app.php               # Configuration settings (GPT-5, etc.)
├── logs/
│   └── debug.log             # Debug logging
├── AI_CHAT_INSTRUCTION.md    # AI chat system prompt guidelines
├── DEPLOYMENT.md             # Deployment instructions
└── composer.json             # Dependencies
```

## 🛠️ API Endpoints

### Web Pages
- `GET /` - Landing page
- `GET /questions` - Question flow interface
- `GET /plan` - Plan display page
- `GET /ai-chat` - AI chat interface
- `GET /reset` - Clear session and start over

### Questionnaire API (For Mobile Apps)
- `GET /api/questionnaire/question/{questionNumber}` - Get specific question
- `POST /api/questionnaire/submit` - Submit answer and get next question
- `GET /api/questionnaire/progress` - Get questionnaire progress
- `POST /api/questionnaire/reset` - Reset questionnaire progress

### AI Chat API (For Mobile Apps)
- `POST /api/ai-chat/send` - Send message to AI coach
- `GET /api/ai-chat/progress` - Get chat progress and readiness for plan
- `POST /api/ai-chat/reset` - Reset chat conversation

### Plan Generation API (For Mobile Apps)
- `POST /api/generate-plan` - Create meditation plan from questionnaire/chat
- `GET /api/current-plan` - Get current plan
- `POST /api/regenerate-plan` - Update plan with feedback
- `GET /api/export/{format}` - Download plan (calendar, html, text, json)

### Debug/Testing API
- `GET /api/questionnaire/debug` - Debug questionnaire state
- `GET /api/questionnaire/test` - Test questionnaire flow
- `POST /api/log` - Frontend logging endpoint

## 🤖 AI Integration

### OpenAI Configuration
- **All Functions**: GPT-5 (latest model)
- **Questions**: GPT-5 (temperature: 0.5) - Structured, consistent questions
- **Plans**: GPT-5 (temperature: 0.7) - Balanced creativity for personalized plans
- **Chat**: GPT-5 (temperature: 0.8) - High creativity for natural conversations
- **Structured outputs**: JSON mode with schema validation
- **Fallback systems**: Static questions and basic plans if AI fails

### Question Strategy (7 Questions + Conditional)
1. **Primary Goal**: What brings you to meditation?
2. **Time Commitment**: How many minutes per week?
3. **Frequency & Guidance**: How often + guided vs self-guided preference
4. **Current Resources**: Where do you find meditation content?
5. **Preferred Tradition**: What type of meditation interests you?
6. **Experience Level**: How comfortable are you with meditation?
7. **Conditional Q6.5**: Challenges for experienced users (only if Q6 ≠ "Beginner")
8. **Specific Challenges**: What obstacles do you face?

### AI Chat System
- **Philosophical Tone**: Reflective, wise, never robotic
- **Conversation Flow**: Opening → Questionnaire → Plan Generation → Follow-up
- **Topic Boundaries**: Avoids sex, politics, religion; redirects to meditation
- **Plan Readiness**: Detects when enough information is gathered
- **Off-topic Management**: Gentle redirection within 3-5 exchanges

## 🎨 Frontend Features

### Design System
- **Color Palette**: Teal (#0d9488) to Emerald (#059669) gradients
- **Typography**: Modern sans-serif with clear hierarchy
- **Components**: Cards, buttons, progress bars with smooth animations
- **Responsive**: Mobile-first design with touch-friendly interfaces

### JavaScript Functionality
- **Theme Switching**: Dark/light mode with localStorage persistence
- **Form Handling**: AJAX submissions with loading states
- **Progress Tracking**: Visual progress updates throughout flow
- **Custom Input**: Toggle between multiple choice and custom text
- **Export Handling**: File download generation
- **Error Handling**: User-friendly error messages with retry options

## 📱 Mobile App API Integration

### Base Configuration
```javascript
const BASE_URL = 'https://your-domain.com';
const API_KEY = 'your-openai-api-key'; // Store securely

// Models Configuration
const QUESTION_MODEL = 'gpt-5';
const PLAN_MODEL = 'gpt-5';
const CHAT_MODEL = 'gpt-5';

// Temperature Settings
const QUESTION_TEMPERATURE = 0.5;
const PLAN_TEMPERATURE = 0.7;
const CHAT_TEMPERATURE = 0.8;
```

### Questionnaire API Usage

#### 1. Get Question
```http
GET /api/questionnaire/question/1
```

**Response:**
```json
{
  "success": true,
  "question": {
    "questionNumber": 1,
    "question": "What is your primary goal for starting a meditation practice?",
    "subtitle": "Understanding your motivation helps us create the perfect plan for you.",
    "type": "single",
    "allowCustomAnswer": true,
    "options": [
      {
        "id": "stress",
        "label": "Stress reduction",
        "description": "Manage daily stress and anxiety"
      }
    ]
  },
  "progress": {
    "currentQuestion": 1,
    "totalQuestions": 8,
    "percentage": 12.5
  }
}
```

#### 2. Submit Answer
```http
POST /api/questionnaire/submit
Content-Type: application/json

{
  "questionNumber": 1,
  "answer": "Stress reduction",
  "isCustom": false,
  "optionId": "stress"
}
```

**Response:**
```json
{
  "success": true,
  "questionNumber": 1,
  "nextQuestion": 2,
  "isComplete": false,
  "progress": 25
}
```

#### 3. Get Progress
```http
GET /api/questionnaire/progress
```

**Response:**
```json
{
  "success": true,
  "currentQuestion": 3,
  "totalQuestions": 8,
  "percentage": 37.5,
  "answers": {
    "1": {
      "question": "What is your primary goal?",
      "answer": "Stress reduction"
    }
  }
}
```

### AI Chat API Usage

#### 1. Send Message
```http
POST /api/ai-chat/send
Content-Type: application/json

{
  "message": "I want to reduce stress and sleep better",
  "history": [
    {
      "type": "user",
      "content": "I want to reduce stress and sleep better",
      "timestamp": "2024-01-15 10:30:00"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "aiMessage": "Every mind deserves a moment of stillness. What brings you to meditation right now?",
  "readyForPlan": false,
  "conversationHistory": [
    {
      "type": "user",
      "content": "I want to reduce stress and sleep better",
      "timestamp": "2024-01-15 10:30:00"
    },
    {
      "type": "ai",
      "content": "Every mind deserves a moment of stillness. What brings you to meditation right now?",
      "timestamp": "2024-01-15 10:30:15"
    }
  ]
}
```

#### 2. Get Chat Progress
```http
GET /api/ai-chat/progress
```

**Response:**
```json
{
  "conversationHistory": [...],
  "readyForPlan": true,
  "messageCount": 12
}
```

### Plan Generation API Usage

#### 1. Generate Plan
```http
POST /api/generate-plan
```

**Response:**
```json
{
  "success": true,
  "plan": {
    "title": "Your Personal Meditation Journey",
    "overview": "A balanced 7-day program designed to introduce you to core meditation practices...",
    "schedule": [
      {
        "day": "Monday",
        "practice": "Foundation Breathing",
        "duration": "5-8 min",
        "description": "Begin with simple breath awareness...",
        "instructions": "Find a comfortable seated position...",
        "coachingNotes": "Remember, it's perfectly normal...",
        "recommendedResources": [
          {
            "name": "Insight Timer",
            "type": "App",
            "reason": "Perfect for beginner breathing meditations",
            "specificContent": "Search for 'beginner breath awareness'",
            "link": "https://insighttimer.com/"
          }
        ]
      }
    ],
    "weeklyReflection": "At the end of this week, reflect on which practices felt most natural...",
    "successTips": [
      "Practice at the same time each day to build consistency",
      "Start with shorter sessions and gradually increase duration"
    ]
  }
}
```

#### 2. Regenerate Plan
```http
POST /api/regenerate-plan
Content-Type: application/json

{
  "feedback": "I'd like more guided meditations and shorter sessions"
}
```

#### 3. Export Plan
```http
GET /api/export/calendar
GET /api/export/html
GET /api/export/text
GET /api/export/json
```

### Error Handling
```json
{
  "error": "Message is required",
  "code": 400
}
```

### Rate Limiting
- **Questionnaire**: No specific limits
- **AI Chat**: 60 requests per minute
- **Plan Generation**: 10 requests per minute

## 📊 Data Models

### Answer Model
```php
class Answer {
    public int $questionNumber;
    public string $question;
    public string $answer;
    public bool $isCustom;
    public ?string $optionId;
    public DateTime $createdAt;
}
```

### MeditationPlan Model
```php
class MeditationPlan {
    public string $title;
    public string $overview;
    public array $schedule; // Array of MeditationDay
    public string $weeklyReflection;
    public array $successTips;
    public DateTime $createdAt;
}
```

### ChatMessage Model
```php
class ChatMessage {
    public string $type; // 'user' or 'ai'
    public string $content;
    public DateTime $timestamp;
}
```

## 🔒 Security & Performance

### Security Features
- Input validation and sanitization
- CSRF protection for forms
- Rate limiting for API calls
- Secure session handling
- Environment variable protection
- No data persistence (privacy-first)

### Performance Optimizations
- Efficient API calls with fallback systems
- Minified CSS/JS assets (via CDN)
- Optimized file structure
- Error handling with graceful degradation

## 🚀 Deployment

### Production Setup
1. **Web Server**: Apache or Nginx with PHP-FPM
2. **PHP Version**: 8.1 or higher
3. **Extensions**: curl, json, mbstring, session
4. **Environment**: Set `APP_ENV=production` in config
5. **Security**: Update OpenAI API key in environment variables

### Docker (Optional)
```dockerfile
FROM php:8.1-apache
COPY . /var/www/html/
RUN a2enmod rewrite
EXPOSE 80
```

## 📝 Usage Flow

### User Journey
1. **Landing Page**: User sees compelling hero section and features
2. **Question Flow**: 7 personalized questions with AI generation
3. **Plan Generation**: AI creates customized 7-day meditation program
4. **Plan Display**: Beautiful interface with export and customization options
5. **Feedback Loop**: Users can provide feedback to regenerate plans

### Export Options
- **Calendar (.ics)**: Adds meditation sessions to user's calendar
- **HTML**: Formatted document for printing/saving
- **Text**: Simple text format for any device
- **JSON**: Structured data for developers

## 🎯 Success Criteria

All requirements from the original specification have been implemented:

### Core Features
- ✅ User can complete full 7-question flow with conditional Q6.5
- ✅ AI generates contextual, personalized questions using GPT-5
- ✅ Custom text input works alongside multiple choice
- ✅ Meditation plans are comprehensive and well-formatted
- ✅ Feedback system successfully regenerates plans
- ✅ All export formats work correctly (calendar, HTML, text, JSON)
- ✅ Interface is fully responsive and accessible

### AI Chat Features
- ✅ Philosophical, reflective AI coach with GPT-5
- ✅ Natural conversation flow with topic boundaries
- ✅ Plan readiness detection and generation
- ✅ Off-topic redirection within 3-5 exchanges
- ✅ Enhanced system prompts for meditation coaching

### Technical Features
- ✅ Application handles errors gracefully
- ✅ Performance is smooth and professional
- ✅ RESTful API for mobile app integration
- ✅ Session-based state management
- ✅ Comprehensive logging and debugging
- ✅ Security features (CSRF, validation, rate limiting)

### Mobile Integration Ready
- ✅ Complete API documentation
- ✅ JSON response formats
- ✅ Error handling patterns
- ✅ Rate limiting guidelines
- ✅ Authentication ready

## 📄 License

This project is built for the Sentient AI Coach platform. Please refer to your licensing agreement for usage terms.

---

**Built with ❤️ using PHP, OpenAI GPT-4, and Tailwind CSS** 