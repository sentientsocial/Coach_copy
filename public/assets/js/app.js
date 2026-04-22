// Sentient AI Coach - Main JavaScript Application

// Theme Management (Light Mode Only)
class ThemeManager {
    constructor() {
        this.init();
    }

    init() {
        // Ensure light mode is always set
        document.documentElement.classList.remove('dark');
        localStorage.removeItem('theme');
    }
}

// Question Flow Management
class QuestionFlow {
    constructor() {
        this.currentQuestion = 1;
        this.selectedOption = null;
        this.isCustom = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadCurrentQuestion();
    }

    setupEventListeners() {
        // Custom toggle
        const customToggle = document.getElementById('custom-toggle');
        if (customToggle) {
            customToggle.addEventListener('click', () => this.toggleCustomInput());
        }

        // Submit answer
        const submitButton = document.getElementById('submit-answer');
        if (submitButton) {
            submitButton.addEventListener('click', () => this.submitAnswer());
        }

        // Generate plan
        const generateButton = document.getElementById('generate-plan');
        if (generateButton) {
            generateButton.addEventListener('click', () => this.generatePlan());
        }

        // Error modal
        const retryButton = document.getElementById('retry-button');
        const closeError = document.getElementById('close-error');
        if (retryButton) retryButton.addEventListener('click', () => this.loadCurrentQuestion());
        if (closeError) closeError.addEventListener('click', () => this.hideError());
    }

    async loadCurrentQuestion() {
        this.showLoading('Generating your next question...');
        
        try {
            const response = await fetch('/api/generate-question', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    questionNumber: this.currentQuestion
                })
            });

            if (!response.ok) {
                throw new Error('Failed to load question');
            }

            const data = await response.json();
            this.displayQuestion(data);
            
        } catch (error) {
            console.error('Error loading question:', error);
            this.showError('Unable to load question. Please try again.');
        }
    }

    displayQuestion(questionData) {
        // Stop loading message rotation and hide loading, show question
        this.stopLoadingMessageRotation();
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('question-container').classList.remove('hidden');

        // Update progress
        this.updateProgress(questionData.questionNumber, questionData.progress);

        // Set question content
        document.getElementById('question-title').textContent = questionData.question;
        document.getElementById('question-subtitle').textContent = questionData.subtitle || '';

        // Create options
        this.createOptions(questionData.options);

        // Reset form state
        this.selectedOption = null;
        this.isCustom = false;
        this.hideCustomInput();
        this.updateSubmitButton();
    }

    createOptions(options) {
        const container = document.getElementById('options-container');
        container.innerHTML = '';

        options.forEach((option, index) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'flex items-end gap-3 animate-fade-in-up';
            optionDiv.style.animationDelay = `${index * 0.1}s`;
            optionDiv.innerHTML = `
                <div class="w-8 h-8 bg-slate-300 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-slate-600 text-sm">👤</span>
                </div>
                <div class="flex-1 max-w-lg">
                    <div class="bg-white rounded-2xl rounded-tl-md p-4 cursor-pointer transition-all duration-200 hover:bg-white hover:shadow-md group border-2 border-transparent hover:border-teal-200" data-option-card>
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="font-medium text-slate-900 group-hover:text-teal-700 transition-colors">
                                    ${option.label}
                                </div>
                                <div class="text-sm text-slate-600 mt-1">
                                    ${option.description}
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <div class="w-5 h-5 border-2 border-slate-400 rounded-full transition-colors group-hover:border-teal-500" data-option-indicator></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const card = optionDiv.querySelector('[data-option-card]');
            card.addEventListener('click', () => this.selectOption(option, optionDiv));
            container.appendChild(optionDiv);
        });
    }

    selectOption(option, element) {
        // Clear previous selection
        document.querySelectorAll('[data-option-card]').forEach(card => {
            card.classList.remove('border-teal-500', 'bg-teal-50', 'bg-teal-900/20');
            card.classList.add('border-transparent');
            const indicator = card.querySelector('[data-option-indicator]');
            indicator.innerHTML = '';
            indicator.classList.remove('border-teal-500', 'bg-teal-500');
            indicator.classList.add('border-slate-400', 'border-slate-500');
        });

        // Mark as selected
        const card = element.querySelector('[data-option-card]');
        const indicator = element.querySelector('[data-option-indicator]');
        
        card.classList.remove('border-transparent');
        card.classList.add('border-teal-500', 'bg-teal-50', 'bg-teal-900/20');
        
        indicator.classList.remove('border-slate-400', 'border-slate-500');
        indicator.classList.add('border-teal-500', 'bg-teal-500');
        indicator.innerHTML = '<svg class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';

        this.selectedOption = option;
        this.isCustom = false;
        this.hideCustomInput();
        this.updateSubmitButton();
    }

    toggleCustomInput() {
        const customArea = document.getElementById('custom-input-area');
        const isHidden = customArea.classList.contains('hidden');

        if (isHidden) {
            // Show custom input
            customArea.classList.remove('hidden');
            document.getElementById('custom-answer').focus();
            
            // Clear option selection
            document.querySelectorAll('[data-option-card]').forEach(card => {
                card.classList.remove('border-teal-500', 'bg-teal-50', 'bg-teal-900/20');
                card.classList.add('border-transparent');
                const indicator = card.querySelector('[data-option-indicator]');
                indicator.innerHTML = '';
                indicator.classList.remove('border-teal-500', 'bg-teal-500');
                indicator.classList.add('border-slate-400', 'border-slate-500');
            });
            
            this.selectedOption = null;
            this.isCustom = true;
        } else {
            // Hide custom input
            this.hideCustomInput();
        }

        this.updateSubmitButton();
    }

    hideCustomInput() {
        document.getElementById('custom-input-area').classList.add('hidden');
        document.getElementById('custom-answer').value = '';
        this.isCustom = false;
    }

    updateSubmitButton() {
        const submitButton = document.getElementById('submit-answer');
        const hasSelection = this.selectedOption || (this.isCustom && document.getElementById('custom-answer').value.trim());
        
        submitButton.disabled = !hasSelection;
        
        if (this.currentQuestion === 7) {
            document.getElementById('submit-text').textContent = 'Complete Questionnaire';
        }
    }

    async submitAnswer() {
        const submitButton = document.getElementById('submit-answer');
        submitButton.disabled = true;
        
        let answer, optionId;
        
        if (this.isCustom) {
            answer = document.getElementById('custom-answer').value.trim();
            optionId = null;
        } else if (this.selectedOption) {
            answer = this.selectedOption.label;
            optionId = this.selectedOption.id;
        } else {
            this.updateSubmitButton();
            return;
        }

        try {
            const response = await fetch('/api/submit-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    questionNumber: this.currentQuestion,
                    question: document.getElementById('question-title').textContent,
                    answer: answer,
                    isCustom: this.isCustom,
                    optionId: optionId
                })
            });

            if (!response.ok) {
                throw new Error('Failed to submit answer');
            }

            const data = await response.json();
            
            if (data.isComplete) {
                // Auto-generate plan after last question
                this.autoGeneratePlan();
            } else {
                this.currentQuestion = data.nextQuestion;
                this.loadCurrentQuestion();
            }
            
        } catch (error) {
            console.error('Error submitting answer:', error);
            this.showError('Unable to save your answer. Please try again.');
            submitButton.disabled = false;
        }
    }

    showCompletion() {
        document.getElementById('question-container').classList.add('hidden');
        document.getElementById('completion-state').classList.remove('hidden');
        this.updateProgress(7, 100);
    }

    async autoGeneratePlan() {
        // Show loading state for plan generation
        this.showLoading('Creating your personalized meditation plan...');
        this.updateProgress(7, 100);
        
        try {
            const response = await fetch('/api/generate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to generate plan');
            }

            // Redirect to plan page
            window.location.href = '/plan';
            
        } catch (error) {
            console.error('Error generating plan:', error);
            this.showError('Unable to generate your plan. Please try again.');
        }
    }

    async generatePlan() {
        const generateButton = document.getElementById('generate-plan');
        const generateText = document.getElementById('generate-text');
        const generateSpinner = document.getElementById('generate-spinner');
        
        generateButton.disabled = true;
        generateText.textContent = 'Generating...';
        generateSpinner.classList.remove('hidden');

        try {
            const response = await fetch('/api/generate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to generate plan');
            }

            // Redirect to plan page
            window.location.href = '/plan';
            
        } catch (error) {
            console.error('Error generating plan:', error);
            this.showError('Unable to generate your plan. Please try again.');
            
            generateButton.disabled = false;
            generateText.textContent = 'Generate My Plan';
            generateSpinner.classList.add('hidden');
        }
    }

    updateProgress(questionNumber, progress) {
        document.getElementById('progress-text').textContent = `Question ${questionNumber} of 7`;
        document.getElementById('progress-percent').textContent = `${Math.round(progress)}%`;
        document.getElementById('progress-bar').style.width = `${progress}%`;
    }

    showLoading(message) {
        document.getElementById('loading-message').textContent = message;
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('question-container').classList.add('hidden');
        document.getElementById('completion-state').classList.add('hidden');
        
        // Rotate loading messages for better UX
        this.startLoadingMessageRotation();
    }
    
    startLoadingMessageRotation() {
        const currentMessage = document.getElementById('loading-message').textContent;
        
        let messages;
        if (currentMessage.includes('meditation plan')) {
            // Plan generation messages
            messages = [
                "Creating your personalized meditation plan...",
                "Analyzing your goals and preferences...",
                "Designing daily meditation sessions...",
                "Crafting coaching notes for you...",
                "Building your 7-day journey...",
                "Tailoring practices to your lifestyle...",
                "Adding personalized success tips..."
            ];
        } else {
            // Question generation messages
            messages = [
                "Analyzing your unique situation...",
                "Crafting personalized question...", 
                "Tailoring options for you...",
                "Building on your responses...",
                "Creating contextual choices...",
                "Adapting to your needs...",
                "Generating smart options..."
            ];
        }
        
        let messageIndex = 0;
        this.loadingInterval = setInterval(() => {
            messageIndex = (messageIndex + 1) % messages.length;
            const loadingElement = document.getElementById('loading-message');
            if (loadingElement) {
                loadingElement.textContent = messages[messageIndex];
            }
        }, 2000);
    }
    
    stopLoadingMessageRotation() {
        if (this.loadingInterval) {
            clearInterval(this.loadingInterval);
            this.loadingInterval = null;
        }
    }

    showError(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-modal').classList.remove('hidden');
        document.getElementById('error-modal').classList.add('flex');
    }

    hideError() {
        document.getElementById('error-modal').classList.add('hidden');
        document.getElementById('error-modal').classList.remove('flex');
    }
}

// Plan Display Management
class PlanDisplay {
    constructor() {
        this.currentPlan = null;
        this.init();
    }

    init() {
        // Check if plan data already exists on the page (server-side rendered)
        const planContainer = document.querySelector('[data-plan-exists="true"]');
        if (planContainer) {
            // Plan is already rendered on the page, get it via AJAX to populate the display
            console.log('Plan already exists on page, loading plan data for display');
            this.loadPlanForDisplay();
        } else {
            // No plan on page, load via AJAX
            this.loadPlan();
        }
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Feedback modal
        const feedbackButton = document.getElementById('feedback-button');
        const closeFeedback = document.getElementById('close-feedback');
        const cancelFeedback = document.getElementById('cancel-feedback');
        const submitFeedback = document.getElementById('submit-feedback');

        if (feedbackButton) feedbackButton.addEventListener('click', () => this.showFeedbackModal());
        if (closeFeedback) closeFeedback.addEventListener('click', () => this.hideFeedbackModal());
        if (cancelFeedback) cancelFeedback.addEventListener('click', () => this.hideFeedbackModal());
        if (submitFeedback) submitFeedback.addEventListener('click', () => this.submitFeedback());
    }

    async loadPlan() {
        try {
            const response = await fetch('/api/current-plan');
            if (!response.ok) {
                throw new Error('Failed to load plan');
            }

            const data = await response.json();
            
            if (data.plan) {
                this.currentPlan = data.plan;
                this.displayPlan(data.plan);
                document.getElementById('plan-loading').style.display = 'none';
            } else {
                // No plan found, redirect to questions
                window.location.href = '/questions';
            }
            
        } catch (error) {
            console.error('Error loading plan:', error);
            // Redirect to home on error
            window.location.href = '/';
        }
    }

    async loadPlanForDisplay() {
        try {
            const response = await fetch('/api/current-plan');
            if (!response.ok) {
                console.warn('Failed to load plan data for display');
                return;
            }

            const data = await response.json();
            
            if (data.plan) {
                this.currentPlan = data.plan;
                this.displayPlan(data.plan);
                document.getElementById('plan-loading').style.display = 'none';
            } else {
                console.warn('No plan data available for display');
            }
            
        } catch (error) {
            console.error('Error loading plan for display:', error);
            // Don't redirect, just log the error since the page is already rendered
        }
    }

    displayPlan(plan) {
        // Set title and overview
        document.getElementById('plan-title').textContent = plan.title;
        document.getElementById('plan-overview').textContent = plan.overview;

        // Display schedule
        this.displaySchedule(plan.schedule);

        // Display reflection and tips
        document.getElementById('weekly-reflection').textContent = plan.weeklyReflection;
        this.displaySuccessTips(plan.successTips);
        
        // Resources are now displayed within individual days
        // this.displayTrustedResources(plan.trustedResources);
    }

    displaySchedule(schedule) {
        const container = document.getElementById('schedule-container');
        container.innerHTML = '';

        schedule.forEach((day, index) => {
            // Build resources HTML if available
            let resourcesHTML = '';
            if (day.recommendedResources && day.recommendedResources.length > 0) {
                const resourceItems = day.recommendedResources.map(resource => `
                    <div class="bg-white border border-slate-200 rounded-lg p-3 mb-2 last:mb-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h6 class="font-medium text-slate-900 text-sm">
                                        ${resource.link ? `<a href="${resource.link}" target="_blank" class="text-teal-600 hover:text-teal-700 hover:underline">${resource.name}</a>` : resource.name}
                                    </h6>
                                    <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">${resource.type}</span>
                                    ${resource.link ? '<svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>' : ''}
                                </div>
                                <p class="text-xs text-slate-600 mb-1">${resource.reason}</p>
                                <p class="text-xs text-slate-500 italic">${resource.specificContent}</p>
                                ${resource.link ? `<p class="text-xs text-blue-600 mt-1"><a href="${resource.link}" target="_blank" class="hover:underline">${resource.link}</a></p>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
                
                resourcesHTML = `
                    <div class="bg-blue-50 rounded-xl p-4 mt-4 border border-blue-200">
                        <h5 class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                            <span class="text-sm">🔗</span> Recommended Resources
                        </h5>
                        <div class="space-y-2">
                            ${resourceItems}
                        </div>
                    </div>
                `;
            }

            const dayDiv = document.createElement('div');
            dayDiv.className = 'flex items-start gap-3 animate-fade-in-up';
            dayDiv.style.animationDelay = `${index * 0.1}s`;
            dayDiv.innerHTML = `
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm">🤖</span>
                </div>
                <div class="flex-1 max-w-3xl">
                    <div class="bg-white rounded-2xl rounded-tl-md p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-3 mb-4">
                            <h3 class="text-xl font-bold text-slate-900">${day.day}</h3>
                            <span class="px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-sm font-medium">${day.duration}</span>
                        </div>
                        <h4 class="text-lg font-semibold text-teal-600 mb-3">${day.practice}</h4>
                        <p class="text-slate-600 mb-4 leading-relaxed">${day.description}</p>
                        
                        <div class="bg-slate-50 rounded-xl p-4 mb-4 border border-slate-200">
                            <h5 class="font-semibold text-slate-900 mb-2 flex items-center gap-2">
                                <span class="text-sm">📝</span> Instructions
                            </h5>
                            <p class="text-slate-600 text-sm leading-relaxed">${day.instructions}</p>
                        </div>
                        
                        <div class="flex items-start gap-2 p-3 bg-teal-50 rounded-lg border border-teal-200">
                            <span class="text-teal-600 text-sm">💡</span>
                            <p class="text-teal-700 text-sm italic leading-relaxed">${day.coachingNotes}</p>
                        </div>
                        
                        ${resourcesHTML}
                    </div>
                </div>
            `;
            container.appendChild(dayDiv);
        });
    }

        displaySuccessTips(tips) {
        const container = document.getElementById('success-tips');
        container.innerHTML = '';
        
        tips.forEach(tip => {
            const tipLi = document.createElement('li');
            tipLi.className = 'flex items-start';
            tipLi.innerHTML = `
                <svg class="w-5 h-5 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-slate-600">${tip}</span>
            `;
            container.appendChild(tipLi);
        });
    }
    
    displayTrustedResources(resources) {
        const container = document.getElementById('trusted-resources');
        const section = document.getElementById('trusted-resources-section');
        
        if (!resources || resources.length === 0) {
            section.style.display = 'none';
            return;
        }
        
        section.style.display = 'flex';
        container.innerHTML = '';
        
        resources.forEach(resource => {
            const resourceDiv = document.createElement('div');
            resourceDiv.className = 'border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow';
            
            const platformBadges = resource.platforms.map(platform => 
                `<span class="inline-block px-2 py-1 text-xs font-medium bg-slate-100 text-slate-600 rounded">${platform}</span>`
            ).join(' ');
            
            const featureList = resource.features.map(feature => 
                `<li class="text-xs text-slate-500">• ${feature}</li>`
            ).join('');
            
            resourceDiv.innerHTML = `
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-slate-900">${resource.name}</h4>
                            <span class="px-2 py-1 text-xs font-medium bg-teal-100 text-teal-700 rounded">${resource.type}</span>
                        </div>
                        <p class="text-sm text-slate-600 mb-2">${resource.description}</p>
                    </div>
                </div>
                <div class="space-y-2">
                    <ul class="space-y-1">
                        ${featureList}
                    </ul>
                    <div class="flex items-center justify-between">
                        <div class="flex gap-1">
                            ${platformBadges}
                        </div>
                        <a href="https://${resource.website}" target="_blank" 
                           class="text-sm text-teal-600 hover:text-teal-700 font-medium flex items-center gap-1">
                            Visit
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            `;
            
            container.appendChild(resourceDiv);
        });
    }

    showFeedbackModal() {
        document.getElementById('feedback-modal').classList.remove('hidden');
        document.getElementById('feedback-modal').classList.add('flex');
        document.getElementById('feedback-text').focus();
    }

    hideFeedbackModal() {
        document.getElementById('feedback-modal').classList.add('hidden');
        document.getElementById('feedback-modal').classList.remove('flex');
        document.getElementById('feedback-text').value = '';
    }

    async submitFeedback() {
        const feedbackText = document.getElementById('feedback-text').value.trim();
        
        if (!feedbackText) {
            return;
        }

        const submitButton = document.getElementById('submit-feedback');
        const submitText = document.getElementById('feedback-submit-text');
        const spinner = document.getElementById('feedback-spinner');

        submitButton.disabled = true;
        submitText.textContent = 'Updating...';
        spinner.classList.remove('hidden');

        try {
            const response = await fetch('/api/regenerate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    feedback: feedbackText
                })
            });

            if (!response.ok) {
                throw new Error('Failed to update plan');
            }

            const data = await response.json();
            
            if (data.plan) {
                this.currentPlan = data.plan;
                this.displayPlan(data.plan);
                this.hideFeedbackModal();
                
                // Show success message
                this.showNotification('Plan updated successfully!', 'success');
            }
            
        } catch (error) {
            console.error('Error updating plan:', error);
            this.showNotification('Unable to update plan. Please try again.', 'error');
        } finally {
            submitButton.disabled = false;
            submitText.textContent = 'Update Plan';
            spinner.classList.add('hidden');
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-primary-500 text-white'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Predefined Questionnaire Flow Management
class QuestionnaireFlow {
    constructor() {
        console.log('QuestionnaireFlow constructor called');
        this.currentQuestion = 1;
        this.selectedOption = null;
        this.answers = {};
        this.init();
    }

    async logToFile(message) {
        try {
            await fetch('/api/log', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: `[FRONTEND] ${message}` })
            });
        } catch (e) {
            // Fallback to console if logging fails
            console.log(message);
        }
    }

    init() {
        console.log('QuestionnaireFlow init called');
        this.setupEventListeners();
        this.loadProgress();
    }

    setupEventListeners() {
        console.log('Setting up event listeners');
        const nextButton = document.getElementById('next-button');
        const backButton = document.getElementById('back-button');
        const generateButton = document.getElementById('generate-plan');

        console.log('Next button:', nextButton);
        console.log('Back button:', backButton);
        console.log('Generate button:', generateButton);

        if (nextButton) {
            nextButton.addEventListener('click', () => this.handleNext());
        }

        if (backButton) {
            backButton.addEventListener('click', () => this.handleBack());
        }

        if (generateButton) {
            generateButton.addEventListener('click', () => this.generatePlan());
        }
    }

    async loadProgress() {
        console.log('Loading progress...');
        try {
            const response = await fetch('/api/questionnaire/progress');
            const data = await response.json();
            console.log('Progress data:', data);

            if (data.isComplete) {
                console.log('Questionnaire is complete, showing completion');
                await this.showCompletion();
            } else {
                console.log('Setting current question to:', data.currentQuestion);
                this.currentQuestion = data.currentQuestion;
                this.loadQuestion();
                this.updateBackButton();
            }
        } catch (error) {
            console.error('Error loading progress:', error);
            this.currentQuestion = 1;
            this.loadQuestion();
            this.updateBackButton();
        }
    }

    async loadQuestion() {
        console.log('loadQuestion called with currentQuestion:', this.currentQuestion);
        
        try {
            const response = await fetch('/api/questionnaire/question', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    questionNumber: this.currentQuestion
                })
            });

            const data = await response.json();
            
            // Check if the request was successful
            if (!response.ok || data.error) {
                console.error('Question not available:', data.error || 'Unknown error');
                console.error('Attempted to load question:', this.currentQuestion);
                
                // This should not happen in normal flow - it indicates a bug
                this.showError('Question not available. This might be a conditional question that was skipped.');
                return;
            }
            
            this.logToFile(`Loading Q${data.questionNumber}: previousAnswer = ${data.previousAnswer || 'none'}`);
            this.logToFile(`Before displayQuestion - customAnswer: "${this.customAnswer}", selectedOption: "${this.selectedOption}", isCustomAnswer: ${this.isCustomAnswer}`);
            
            this.displayQuestion(data);
            
            this.logToFile(`After displayQuestion - customAnswer: "${this.customAnswer}", selectedOption: "${this.selectedOption}", isCustomAnswer: ${this.isCustomAnswer}`);
            this.updateProgress();
            
            // Store previous question for back navigation
            this.previousQuestion = data.previousQuestion;
            
            // Add small delay to ensure DOM is ready
            setTimeout(() => {
                this.updateBackButton();
            }, 100);
        } catch (error) {
            console.error('Error loading question:', error);
            this.showError('Unable to load question. Please try again.');
        }
    }

    displayQuestion(data) {
        const titleElement = document.getElementById('question-title');
        const optionsContainer = document.getElementById('options-container');
        
        if (titleElement) {
            titleElement.textContent = data.title;
        }

        if (optionsContainer) {
            optionsContainer.innerHTML = '';

            // Check if this is a text-only question
            if (data.textOnly) {
                // For text-only questions, only show the custom answer field
                const textOnlyElement = this.createTextOnlyElement();
                optionsContainer.appendChild(textOnlyElement);
                
                this.selectedOption = null;
                this.customAnswer = '';
                this.isCustomAnswer = true; // Always custom for text-only questions
                
                // Restore previous answer if it exists
                if (data.previousAnswer) {
                    this.restoreTextOnlyAnswer(data.previousAnswer);
                }
            }
            // Check if this is a multi-select question (Question 3)
            else if (data.multiSelect && data.groups) {
                this.selectedOptions = {};
                data.groups.forEach((group, groupIndex) => {
                    this.selectedOptions[groupIndex] = null;
                    const groupElement = this.createGroupElement(group, groupIndex);
                    optionsContainer.appendChild(groupElement);
                });
                
                // Add custom answer option for multi-select if allowed
                if (data.allowCustomAnswer) {
                    const customElement = this.createCustomAnswerElementMultiSelect();
                    optionsContainer.appendChild(customElement);
                }
                
                // Reset ALL state for multi-select questions
                this.selectedOption = null; // Clear single-select state
                this.customAnswer = '';
                this.isCustomAnswer = false;
                
                // Restore previous selections if they exist (only if this question was answered before)
                if (data.previousAnswer) {
                    this.restoreMultiSelectAnswer(data.previousAnswer);
                }
            } else {
                // Regular single-select question
                data.options.forEach((option, index) => {
                    const optionElement = this.createOptionElement(option, index);
                    optionsContainer.appendChild(optionElement);
                });
                
                // Add custom answer option if allowed
                if (data.allowCustomAnswer) {
                    const customElement = this.createCustomAnswerElement();
                    optionsContainer.appendChild(customElement);
                }
                
                // Reset ALL state for single-select questions
                this.selectedOption = null;
                this.customAnswer = '';
                this.isCustomAnswer = false;
                this.selectedOptions = null; // Clear multi-select state
                
                // Restore previous selection if it exists
                if (data.previousAnswer) {
                    this.restoreSingleSelectAnswer(data.previousAnswer);
                }
            }
        }

        this.updateNextButton();
        
        // Force update back button visibility after DOM is ready
        setTimeout(() => {
            this.updateBackButton();
        }, 50);
    }

    createGroupElement(group, groupIndex) {
        const groupDiv = document.createElement('div');
        groupDiv.className = 'mb-6';
        
        groupDiv.innerHTML = `
            <h4 class="text-lg font-medium text-slate-800 mb-3">${group.label}</h4>
            <div class="space-y-2" data-group="${groupIndex}">
            </div>
        `;

        const optionsContainer = groupDiv.querySelector('[data-group]');
        
        group.options.forEach((option, optionIndex) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.innerHTML = `
                <button type="button" 
                        class="w-full p-3 text-left border-2 border-slate-200 rounded-lg hover:border-teal-300 transition-colors bg-white hover:bg-slate-50"
                        data-group="${groupIndex}" data-option="${option}">
                    <span class="text-slate-900">${option}</span>
                </button>
            `;

            optionDiv.querySelector('button').addEventListener('click', () => {
                this.selectGroupOption(option, optionDiv, groupIndex);
            });

            optionsContainer.appendChild(optionDiv);
        });

        return groupDiv;
    }

    createOptionElement(option, index) {
        const div = document.createElement('div');
        div.className = 'option-item';
        div.innerHTML = `
            <button type="button" 
                    class="w-full p-4 text-left border-2 border-slate-200 rounded-xl hover:border-teal-300 transition-colors bg-white hover:bg-slate-50"
                    data-option="${option}">
                <span class="text-slate-900">${option}</span>
            </button>
        `;

        div.querySelector('button').addEventListener('click', () => {
            this.selectOption(option, div);
        });

        return div;
    }

    createCustomAnswerElement() {
        const div = document.createElement('div');
        div.className = 'option-item custom-answer-container';
        div.innerHTML = `
            <button type="button" 
                    class="w-full p-4 text-left border-2 border-slate-200 rounded-xl hover:border-teal-300 transition-colors bg-white hover:bg-slate-50"
                    id="custom-answer-button">
                <span class="text-slate-900">✍️ Write your own answer</span>
            </button>
            <div class="mt-3 hidden" id="custom-answer-input-container">
                <textarea 
                    id="custom-answer-input"
                    placeholder="Share your thoughts in your own words..."
                    class="w-full p-3 border-2 border-teal-300 rounded-lg focus:border-teal-500 focus:outline-none resize-none"
                    rows="3"
                    maxlength="500"></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-slate-500">Max 500 characters</span>
                    <span class="text-xs text-slate-500" id="char-count">0/500</span>
                </div>
            </div>
        `;

        const button = div.querySelector('#custom-answer-button');
        const inputContainer = div.querySelector('#custom-answer-input-container');
        const textarea = div.querySelector('#custom-answer-input');
        const charCount = div.querySelector('#char-count');

        button.addEventListener('click', () => {
            this.selectCustomAnswer(button, inputContainer, textarea);
        });

        textarea.addEventListener('input', () => {
            const length = textarea.value.length;
            charCount.textContent = `${length}/500`;
            this.customAnswer = textarea.value.trim();
            this.updateNextButton();
        });

        return div;
    }

    createTextOnlyElement() {
        const div = document.createElement('div');
        div.className = 'text-only-container';
        div.innerHTML = `
            <div class="bg-slate-50 rounded-xl p-4 border-2 border-slate-200">
                <textarea 
                    id="text-only-input"
                    placeholder="Please share your thoughts..."
                    class="w-full p-4 border-2 border-slate-300 rounded-lg focus:border-teal-500 focus:outline-none resize-none bg-white"
                    rows="4"
                    maxlength="1000"></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-slate-500">Share your experience in detail</span>
                    <span class="text-xs text-slate-500" id="char-count-textonly">0/1000</span>
                </div>
            </div>
        `;

        const textarea = div.querySelector('#text-only-input');
        const charCount = div.querySelector('#char-count-textonly');

        textarea.addEventListener('input', () => {
            const length = textarea.value.length;
            charCount.textContent = `${length}/1000`;
            this.customAnswer = textarea.value.trim();
            this.updateNextButton();
        });

        // Auto-focus the textarea
        setTimeout(() => {
            textarea.focus();
        }, 100);

        return div;
    }

    restoreTextOnlyAnswer(previousAnswer) {
        const textarea = document.getElementById('text-only-input');
        const charCount = document.getElementById('char-count-textonly');
        
        if (textarea && charCount) {
            textarea.value = previousAnswer;
            charCount.textContent = `${previousAnswer.length}/1000`;
            this.customAnswer = previousAnswer;
            this.isCustomAnswer = true;
        }
    }

    createCustomAnswerElementMultiSelect() {
        const div = document.createElement('div');
        div.className = 'mt-6 p-4 border-2 border-slate-200 rounded-xl bg-slate-50';
        div.innerHTML = `
            <button type="button" id="custom-answer-btn-multiselect" class="w-full text-left">
                <h4 class="text-lg font-medium text-slate-800 mb-1">✍️ Or write your own answer</h4>
                <p class="text-sm text-slate-600">Click to add a custom response</p>
            </button>
            <div id="custom-answer-input-multiselect" class="mt-3 hidden">
                <textarea 
                    id="custom-answer-multiselect"
                    placeholder="Share your thoughts in your own words..."
                    class="w-full p-3 border-2 border-slate-300 rounded-lg focus:border-teal-500 focus:outline-none resize-none bg-white"
                    rows="3"
                    maxlength="500"></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-slate-500">This will replace all previous selections</span>
                    <span class="text-xs text-slate-500" id="char-count-multiselect">0/500</span>
                </div>
            </div>
        `;

        const button = div.querySelector('#custom-answer-btn-multiselect');
        const inputContainer = div.querySelector('#custom-answer-input-multiselect');
        const textarea = div.querySelector('#custom-answer-multiselect');
        const charCount = div.querySelector('#char-count-multiselect');

        button.addEventListener('click', () => {
            inputContainer.classList.remove('hidden');
            button.classList.add('hidden');
            textarea.focus();
            this.selectCustomAnswerMultiSelect();
        });

        textarea.addEventListener('input', () => {
            const length = textarea.value.length;
            charCount.textContent = `${length}/500`;
            this.customAnswer = textarea.value.trim();
            
            if (this.customAnswer.length > 0) {
                // Clear all group selections when user types custom answer
                this.selectedOptions = {};
                document.querySelectorAll('.option-item button').forEach(btn => {
                    btn.classList.remove('border-teal-500', 'bg-teal-50');
                    btn.classList.add('border-slate-200');
                });
                this.isCustomAnswer = true;
            } else {
                this.isCustomAnswer = false;
            }
            
            this.updateNextButton();
        });

        return div;
    }

    selectCustomAnswerMultiSelect() {
        // Clear all group selections when custom answer is selected
        this.selectedOptions = {};
        document.querySelectorAll('.option-item button').forEach(btn => {
            btn.classList.remove('border-teal-500', 'bg-teal-50');
            btn.classList.add('border-slate-200');
        });
        this.isCustomAnswer = true;
        this.updateNextButton();
    }

    selectGroupOption(option, element, groupIndex) {
        // Remove previous selection within this group
        document.querySelectorAll(`[data-group="${groupIndex}"] button`).forEach(btn => {
            btn.classList.remove('border-teal-500', 'bg-teal-50');
            btn.classList.add('border-slate-200');
        });

        // Add selection to clicked option
        element.querySelector('button').classList.remove('border-slate-200');
        element.querySelector('button').classList.add('border-teal-500', 'bg-teal-50');

        this.selectedOptions[groupIndex] = option;
        
        // Clear custom answer when selecting predefined options
        const customTextarea = document.getElementById('custom-answer-multiselect');
        if (customTextarea) {
            customTextarea.value = '';
            this.customAnswer = '';
            this.isCustomAnswer = false;
            const charCount = document.getElementById('char-count-multiselect');
            if (charCount) {
                charCount.textContent = '0/500';
            }
            
            // Hide custom input and show button again
            const customButton = document.getElementById('custom-answer-btn-multiselect');
            const customInput = document.getElementById('custom-answer-input-multiselect');
            if (customButton && customInput) {
                customButton.classList.remove('hidden');
                customInput.classList.add('hidden');
            }
        }
        
        this.updateNextButton();
    }

    selectOption(option, element) {
        // Remove previous selection
        document.querySelectorAll('.option-item button').forEach(btn => {
            btn.classList.remove('border-teal-500', 'bg-teal-50');
            btn.classList.add('border-slate-200');
        });

        // Hide custom answer input if it was active
        const customInputContainer = document.getElementById('custom-answer-input-container');
        if (customInputContainer) {
            customInputContainer.classList.add('hidden');
        }

        // Add selection to clicked option
        element.querySelector('button').classList.remove('border-slate-200');
        element.querySelector('button').classList.add('border-teal-500', 'bg-teal-50');

        this.selectedOption = option;
        this.isCustomAnswer = false;
        this.customAnswer = '';
        this.updateNextButton();
    }

    selectCustomAnswer(button, inputContainer, textarea) {
        // Remove previous selections from other options
        document.querySelectorAll('.option-item button').forEach(btn => {
            btn.classList.remove('border-teal-500', 'bg-teal-50');
            btn.classList.add('border-slate-200');
        });

        // Activate custom answer button
        button.classList.remove('border-slate-200');
        button.classList.add('border-teal-500', 'bg-teal-50');

        // Show input container
        inputContainer.classList.remove('hidden');
        
        // Focus on textarea
        setTimeout(() => {
            textarea.focus();
        }, 100);

        this.selectedOption = null;
        this.isCustomAnswer = true;
        this.customAnswer = textarea.value.trim();
        this.updateNextButton();
    }

    restoreMultiSelectAnswer(previousAnswer) {
        // previousAnswer format: "3 times | Guided meditation"
        const parts = previousAnswer.split(' | ');
        let foundMatches = 0;
        
        if (parts.length >= 2) {
            // Find and select the frequency option (first part)
            const frequencyButtons = document.querySelectorAll('[data-group="0"] button');
            frequencyButtons.forEach(btn => {
                if (btn.dataset.option === parts[0]) {
                    btn.classList.remove('border-slate-200');
                    btn.classList.add('border-teal-500', 'bg-teal-50');
                    this.selectedOptions[0] = parts[0];
                    foundMatches++;
                }
            });
            
            // Find and select the guidance option (second part)
            const guidanceButtons = document.querySelectorAll('[data-group="1"] button');
            guidanceButtons.forEach(btn => {
                if (btn.dataset.option === parts[1]) {
                    btn.classList.remove('border-slate-200');
                    btn.classList.add('border-teal-500', 'bg-teal-50');
                    this.selectedOptions[1] = parts[1];
                    foundMatches++;
                }
            });
        }
        
        // If no matches found in predefined options, treat as custom answer
        // For multi-select, we expect custom answers to either contain " | " or be a single custom text
        if (foundMatches === 0) {
            const customTextarea = document.getElementById('custom-answer-multiselect');
            const customButton = document.getElementById('custom-answer-btn-multiselect');
            const customInput = document.getElementById('custom-answer-input-multiselect');
            
            if (customTextarea) {
                // Show the custom input and hide the button
                if (customButton && customInput) {
                    customButton.classList.add('hidden');
                    customInput.classList.remove('hidden');
                }
                
                customTextarea.value = previousAnswer;
                
                // Update character count
                const charCount = document.getElementById('char-count-multiselect');
                if (charCount) {
                    charCount.textContent = `${previousAnswer.length}/500`;
                }
                
                this.customAnswer = previousAnswer;
                this.isCustomAnswer = true;
                this.selectedOptions = {}; // Clear predefined selections
            }
        } else {
            this.isCustomAnswer = false;
        }
    }
    
    restoreSingleSelectAnswer(previousAnswer) {
        this.logToFile(`restoreSingleSelectAnswer called with: "${previousAnswer}"`);
        
        // First try to match with predefined options
        const buttons = document.querySelectorAll('.option-item button:not(#custom-answer-button)');
        let foundMatch = false;
        
        buttons.forEach(btn => {
            if (btn.dataset.option === previousAnswer) {
                btn.classList.remove('border-slate-200');
                btn.classList.add('border-teal-500', 'bg-teal-50');
                this.selectedOption = previousAnswer;
                this.isCustomAnswer = false;
                foundMatch = true;
            }
        });
        
        // If no match found in predefined options, treat as custom answer
        // But first check if this looks like a multi-select answer (contains " | ")
        // which shouldn't be restored in single-select questions
        if (!foundMatch && !previousAnswer.includes(' | ')) {
            const customButton = document.getElementById('custom-answer-button');
            const customInputContainer = document.getElementById('custom-answer-input-container');
            const customTextarea = document.getElementById('custom-answer-input');
            
            if (customButton && customInputContainer && customTextarea) {
                // Activate custom answer option
                customButton.classList.remove('border-slate-200');
                customButton.classList.add('border-teal-500', 'bg-teal-50');
                
                // Show input and set value
                customInputContainer.classList.remove('hidden');
                customTextarea.value = previousAnswer;
                
                // Update character count
                const charCount = document.getElementById('char-count');
                if (charCount) {
                    charCount.textContent = `${previousAnswer.length}/500`;
                }
                
                this.selectedOption = null;
                this.isCustomAnswer = true;
                this.customAnswer = previousAnswer;
            }
        }
    }

    updateBackButton() {
        const backButton = document.getElementById('back-button');
        if (backButton) {
            // Show back button only if there's a valid previous question
            if (this.previousQuestion && this.previousQuestion !== null) {
                backButton.classList.remove('hidden');
                backButton.style.display = 'flex';
                backButton.disabled = false;
            } else {
                backButton.classList.add('hidden');
                backButton.style.display = 'none';
                backButton.disabled = true;
            }
        }
    }

    updateNextButton() {
        const nextButton = document.getElementById('next-button');
        if (nextButton) {
            // Check if this is a text-only question
            if (document.querySelector('.text-only-container')) {
                nextButton.disabled = !(this.customAnswer.length > 0);
            }
            // Check if this is a multi-select question
            else if (this.selectedOptions) {
                // For multi-select, either all groups must have a selection OR custom answer is provided
                const allSelected = Object.values(this.selectedOptions).every(option => option !== null);
                const hasCustomAnswer = this.isCustomAnswer && this.customAnswer.length > 0;
                nextButton.disabled = !allSelected && !hasCustomAnswer;
            } else {
                // For single-select questions, check either predefined option or custom answer
                const hasValidAnswer = this.selectedOption || (this.isCustomAnswer && this.customAnswer.length > 0);
                nextButton.disabled = !hasValidAnswer;
            }
        }
    }

    async handleNext() {
        let answer;
        let isCustomAnswer = false;
        
        // Check if this is a text-only question
        if (document.querySelector('.text-only-container')) {
            if (this.customAnswer.length > 0) {
                answer = this.customAnswer;
                isCustomAnswer = true;
            } else {
                return; // No valid answer for text-only question
            }
        }
        // Check if this is a multi-select question
        else if (this.selectedOptions) {
            // Check if using custom answer or predefined selections
            if (this.isCustomAnswer && this.customAnswer.length > 0) {
                answer = this.customAnswer;
                isCustomAnswer = true;
            } else {
                const allSelected = Object.values(this.selectedOptions).every(option => option !== null);
                if (!allSelected) return;
                
                // Combine all selected options into a single answer
                answer = Object.values(this.selectedOptions).join(' | ');
                isCustomAnswer = false;
            }
        } else {
            // For single-select, check if using custom answer or predefined option
            if (this.isCustomAnswer && this.customAnswer.length > 0) {
                answer = this.customAnswer;
                isCustomAnswer = true;
                this.logToFile(`Q${this.currentQuestion} - Using custom answer: "${answer}"`);
            } else if (this.selectedOption) {
                answer = this.selectedOption;
                isCustomAnswer = false;
                this.logToFile(`Q${this.currentQuestion} - Using selected option: "${answer}"`);
            } else {
                this.logToFile(`Q${this.currentQuestion} - No valid answer found. isCustomAnswer: ${this.isCustomAnswer}, customAnswer: "${this.customAnswer}", selectedOption: "${this.selectedOption}"`);
                return; // No valid answer
            }
        }

        try {
            const response = await fetch('/api/questionnaire/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    questionNumber: this.currentQuestion,
                    answer: answer,
                    isCustomAnswer: isCustomAnswer
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('Submit response:', data);
                console.log('Next question from backend:', data.nextQuestion);
                
                if (data.isComplete) {
                    await this.showCompletion();
                } else {
                    this.currentQuestion = data.nextQuestion;
                    console.log('Setting currentQuestion to:', this.currentQuestion);
                    this.loadQuestion();
                }
            }
        } catch (error) {
            console.error('Error submitting answer:', error);
            this.showError('Unable to save answer. Please try again.');
        }
    }

    async handleBack() {
        // Use the previousQuestion from the current question's data
        if (this.previousQuestion && this.previousQuestion !== null) {
            this.currentQuestion = this.previousQuestion;
            this.loadQuestion();
            this.updateBackButton();
        }
    }

    updateProgress() {
        const progressText = document.getElementById('progress-text');
        const progressPercent = document.getElementById('progress-percent');
        const progressBar = document.getElementById('progress-bar');

        if (progressText) {
            progressText.textContent = `Question ${this.currentQuestion} of 8`;
        }

        const progress = Math.round(((this.currentQuestion - 1) / 8) * 100);

        if (progressPercent) {
            progressPercent.textContent = `${progress}%`;
        }

        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
    }

    async showCompletion() {
        const questionContainer = document.getElementById('question-container');
        const completionState = document.getElementById('completion-state');

        if (questionContainer) {
            questionContainer.classList.add('hidden');
        }

        if (completionState) {
            completionState.classList.remove('hidden');
        }

        // Update progress to 100%
        const progressPercent = document.getElementById('progress-percent');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        if (progressPercent) progressPercent.textContent = '100%';
        if (progressBar) progressBar.style.width = '100%';
        if (progressText) progressText.textContent = 'Complete!';

        // Check if plan already exists and update UI accordingly
        try {
            const planCheckResponse = await fetch('/api/current-plan');
            const planData = await planCheckResponse.json();
            
            const generateButton = document.getElementById('generate-plan');
            const generateText = document.getElementById('generate-text');
            const completionMessage = completionState.querySelector('p');
            
            if (planData.plan) {
                // Plan exists - change button to "View My Plan"
                if (generateText) generateText.textContent = 'View My Plan';
                if (completionMessage) {
                    completionMessage.textContent = 'You already have a meditation plan. Click below to view it.';
                }
                const generateButton = document.getElementById('generate-plan');
                if (generateButton) {
                    generateButton.onclick = () => { window.location.href = '/plan'; };
                }
            } else {
                // No plan - show create plan option
                if (generateText) generateText.textContent = 'Create My Plan';
                if (completionMessage) {
                    completionMessage.textContent = 'Thank you for completing the questionnaire. I\'ll now create a personalized meditation plan based on your answers.';
                }
            }
        } catch (error) {
            console.error('Error checking plan status:', error);
            // Keep default text if check fails
        }
    }

    async generatePlan() {
        const generateButton = document.getElementById('generate-plan');
        const generateText = document.getElementById('generate-text');
        const generateSpinner = document.getElementById('generate-spinner');

        if (generateButton) generateButton.disabled = true;
        if (generateText) generateText.textContent = 'Creating Plan...';
        if (generateSpinner) generateSpinner.classList.remove('hidden');

        try {
            // Check if plan already exists
            const planCheckResponse = await fetch('/api/current-plan');
            const planData = await planCheckResponse.json();
            
            if (planData.plan) {
                // Plan already exists, go to plan page
                window.location.href = '/plan';
                return;
            }

            // No plan exists, create new one
            const response = await fetch('/api/generate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                window.location.href = '/plan';
            }
        } catch (error) {
            console.error('Error generating plan:', error);
            this.showError('Unable to generate plan. Please try again.');
        } finally {
            if (generateButton) generateButton.disabled = false;
            if (generateText) generateText.textContent = 'Create My Plan';
            if (generateSpinner) generateSpinner.classList.add('hidden');
        }
    }

    async resetAndGoHome() {
        try {
            // Reset the questionnaire
            await fetch('/api/questionnaire/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            // Redirect to home
            window.location.href = '/';
        } catch (error) {
            console.error('Error resetting questionnaire:', error);
            // Still redirect to home even if reset fails
            window.location.href = '/';
        }
    }

    showError(message) {
        const errorModal = document.getElementById('error-modal');
        const errorMessage = document.getElementById('error-message');

        if (errorMessage) {
            errorMessage.textContent = message;
        }

        if (errorModal) {
            errorModal.classList.remove('hidden');
            errorModal.classList.add('flex');
        }
    }

    updateBackButton() {
        const backButton = document.getElementById('back-button');
        if (backButton) {
            backButton.disabled = this.currentQuestion <= 1;
        }
    }
}

// AI Chat Flow Management
class AiChatFlow {
    constructor() {
        this.conversationHistory = [];
        this.readyForPlan = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadProgress();
    }

    setupEventListeners() {
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const createPlanButton = document.getElementById('create-plan-button');

        if (messageInput) {
            messageInput.addEventListener('input', () => this.updateSendButton());
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }

        if (sendButton) {
            sendButton.addEventListener('click', () => this.sendMessage());
        }

        if (createPlanButton) {
            createPlanButton.addEventListener('click', () => this.generatePlan());
        }
    }

    async loadProgress() {
        try {
            const response = await fetch('/api/ai-chat/progress');
            const data = await response.json();

            this.conversationHistory = data.conversationHistory || [];
            this.readyForPlan = data.readyForPlan || false;

            this.displayMessages();

            if (this.readyForPlan) {
                this.showCompletion();
            } else {
                this.enableInput();
                // If no conversation yet, start with AI greeting
                if (this.conversationHistory.length === 0) {
                    this.getInitialMessage();
                }
            }
        } catch (error) {
            console.error('Error loading progress:', error);
            this.enableInput();
            this.getInitialMessage();
        }
    }

    async getInitialMessage() {
        this.showTypingIndicator();

        try {
            const response = await fetch('/api/ai-chat/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    history: []
                })
            });

            const data = await response.json();
            this.hideTypingIndicator();
            
            if (data.message) {
                this.addAiMessage(data.message);
            }
        } catch (error) {
            console.error('Error getting initial message:', error);
            this.hideTypingIndicator();
            this.addAiMessage("Hello! I'm Aria, your meditation coach. I'm here to help you discover a meditation practice that fits perfectly into your life. What brings you to meditation today? Are you looking to reduce stress, improve focus, or something else?");
        }
    }

    async sendMessage() {
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();

        if (!message || this.readyForPlan) return;

        // Add user message
        this.addUserMessage(message);
        messageInput.value = '';
        this.updateSendButton();

        // Send message to AI
        this.showTypingIndicator();

        try {
            const response = await fetch('/api/ai-chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    history: this.conversationHistory
                })
            });

            const data = await response.json();
            this.hideTypingIndicator();

            if (data.success) {
                // Update conversation history
                this.conversationHistory = data.conversationHistory;
                
                // Add AI response
                if (data.aiMessage) {
                    this.addAiMessage(data.aiMessage);
                }

                // Check if ready for plan
                if (data.readyForPlan) {
                    this.readyForPlan = true;
                    this.showCompletion();
                }
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.hideTypingIndicator();
            this.addAiMessage("I apologize for the technical hiccup! Let me try to respond to what you shared. Could you tell me a bit more about what you're hoping to get from meditation?");
        }
    }

    addAiMessage(content) {
        const messagesContainer = document.getElementById('messages-container');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3 animate-fade-in-up';
        
        messageDiv.innerHTML = `
            <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-sm">🤖</span>
            </div>
            <div class="flex-1 max-w-lg">
                <div class="bg-white rounded-2xl rounded-tl-md p-4 shadow-sm border border-slate-200">
                    <p class="text-slate-700">${content}</p>
                </div>
                <div class="text-xs text-slate-500 mt-1 ml-2">AI Coach • Just now</div>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    addUserMessage(content) {
        const messagesContainer = document.getElementById('messages-container');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3 justify-end animate-fade-in-up';
        
        messageDiv.innerHTML = `
            <div class="flex-1 max-w-lg">
                <div class="bg-teal-600 text-white rounded-2xl rounded-tr-md p-4 shadow-sm ml-auto">
                    <p>${content}</p>
                </div>
                <div class="text-xs text-slate-500 mt-1 mr-2 text-right">You • Just now</div>
            </div>
            <div class="w-8 h-8 bg-slate-300 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-slate-600 text-sm">👤</span>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    displayMessages() {
        const messagesContainer = document.getElementById('messages-container');
        
        // Clear existing messages (except welcome message)
        const welcomeMessage = messagesContainer.querySelector('.animate-fade-in-up');
        messagesContainer.innerHTML = '';
        if (welcomeMessage) {
            messagesContainer.appendChild(welcomeMessage);
        }
        
        this.conversationHistory.forEach(message => {
            if (message.type === 'ai') {
                this.addAiMessage(message.content);
            } else {
                this.addUserMessage(message.content);
            }
        });
    }

    showTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.classList.remove('hidden');
            typingIndicator.classList.add('flex');
        }
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.classList.add('hidden');
            typingIndicator.classList.remove('flex');
        }
    }

    enableInput() {
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            messageInput.disabled = false;
            messageInput.focus();
        }
        this.updateSendButton();
    }

    updateSendButton() {
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        
        if (sendButton && messageInput) {
            sendButton.disabled = !messageInput.value.trim() || this.readyForPlan;
        }
    }

    showCompletion() {
        const completionModal = document.getElementById('completion-modal');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');

        if (messageInput) {
            messageInput.disabled = true;
            messageInput.placeholder = "Ready to create your plan!";
        }
        if (sendButton) sendButton.disabled = true;

        if (completionModal) {
            completionModal.classList.remove('hidden');
            completionModal.classList.add('flex');
        }
    }

    async generatePlan() {
        const createPlanButton = document.getElementById('create-plan-button');
        const createPlanText = document.getElementById('create-plan-text');
        const createPlanSpinner = document.getElementById('create-plan-spinner');

        if (createPlanButton) createPlanButton.disabled = true;
        if (createPlanText) createPlanText.textContent = 'Creating Plan...';
        if (createPlanSpinner) createPlanSpinner.classList.remove('hidden');

        try {
            const response = await fetch('/api/generate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                window.location.href = '/plan';
            }
        } catch (error) {
            console.error('Error generating plan:', error);
        } finally {
            if (createPlanButton) createPlanButton.disabled = false;
            if (createPlanText) createPlanText.textContent = 'Create My Plan';
            if (createPlanSpinner) createPlanSpinner.classList.add('hidden');
        }
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

console.log('JavaScript file loaded successfully');

// Manual back button fix - force show on Question 2+
function forceUpdateBackButton() {
    const backButton = document.getElementById('back-button');
    const progressText = document.getElementById('progress-text');
    
    if (backButton && progressText) {
        const currentQuestionMatch = progressText.textContent.match(/Question (\d+)/);
        if (currentQuestionMatch) {
            const currentQuestion = parseInt(currentQuestionMatch[1]);
            
            if (currentQuestion > 1) {
                backButton.classList.remove('hidden');
                backButton.style.display = 'flex';
                backButton.disabled = false;
            } else {
                backButton.classList.add('hidden');
                backButton.style.display = 'none';
                backButton.disabled = true;
            }
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Initialize theme manager
    new ThemeManager();
    
    const questionContainer = document.getElementById('question-container');
    const customToggle = document.getElementById('custom-toggle');
    const messagesContainer = document.getElementById('messages-container');
    
    console.log('Elements found:');
    console.log('  question-container:', questionContainer);
    console.log('  custom-toggle:', customToggle);
    console.log('  messages-container:', messagesContainer);
    
    // Initialize question flow on questions page (legacy AI flow)
    if (questionContainer && customToggle) {
        console.log('Initializing legacy QuestionFlow');
        window.QuestionFlow = new QuestionFlow();
    }
    
    // Initialize predefined questionnaire flow
    if (questionContainer && !customToggle) {
        console.log('Initializing QuestionnaireFlow');
        window.QuestionnaireFlow = new QuestionnaireFlow();
    }
    
    // Initialize AI chat flow
    if (document.getElementById('messages-container')) {
        window.AiChatFlow = new AiChatFlow();
    }
    
    // Initialize plan display on plan page
    if (document.getElementById('schedule-container')) {
        window.PlanDisplay = new PlanDisplay();
    }
    
    // Force back button update on questionnaire page
    if (questionContainer && !customToggle) {
        console.log('Setting up back button monitoring for questionnaire');
        
        // Initial check
        setTimeout(forceUpdateBackButton, 500);
        
        // Monitor for changes every 1 second
        setInterval(forceUpdateBackButton, 1000);
        
        // Also monitor when progress text changes
        const progressText = document.getElementById('progress-text');
        if (progressText) {
            const observer = new MutationObserver(function(mutations) {
                console.log('Progress text changed, updating back button');
                setTimeout(forceUpdateBackButton, 100);
            });
            observer.observe(progressText, { childList: true, subtree: true });
        }
    }

    // Add custom answer monitoring (legacy flow)
    const customAnswer = document.getElementById('custom-answer');
    if (customAnswer) {
        customAnswer.addEventListener('input', debounce(() => {
            if (window.QuestionFlow) {
                window.QuestionFlow.updateSubmitButton();
            }
        }, 300));
    }
    
    // Add error modal handlers
    const closeError = document.getElementById('close-error');
    const retryButton = document.getElementById('retry-button');
    
    if (closeError) {
        closeError.addEventListener('click', () => {
            const errorModal = document.getElementById('error-modal');
            if (errorModal) {
                errorModal.classList.add('hidden');
                errorModal.classList.remove('flex');
            }
        });
    }
    
    if (retryButton) {
        retryButton.addEventListener('click', () => {
            const errorModal = document.getElementById('error-modal');
            if (errorModal) {
                errorModal.classList.add('hidden');
                errorModal.classList.remove('flex');
            }
            
            // Retry based on current flow
            if (window.QuestionnaireFlow) {
                window.QuestionnaireFlow.loadQuestion();
            } else if (window.AiChatFlow) {
                window.AiChatFlow.askNextQuestion();
            } else if (window.QuestionFlow) {
                window.QuestionFlow.loadCurrentQuestion();
            }
        });
    }
});

// Global export function
function exportPlan(format) {
    try {
        // Create a temporary link to trigger download
        const link = document.createElement('a');
        link.href = `/api/export/${format}`;
        link.download = ''; // Let the server determine the filename
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success notification if PlanDisplay is available
        if (window.PlanDisplay && window.PlanDisplay.showNotification) {
            window.PlanDisplay.showNotification(`Downloading ${format} export...`, 'success');
        }
    } catch (error) {
        console.error('Export error:', error);
        
        // Show error notification if PlanDisplay is available
        if (window.PlanDisplay && window.PlanDisplay.showNotification) {
            window.PlanDisplay.showNotification('Download failed. Please try again.', 'error');
        }
    }
}

// Export for global access
window.ThemeManager = ThemeManager;
window.QuestionFlow = QuestionFlow;
window.QuestionnaireFlow = QuestionnaireFlow;
window.AiChatFlow = AiChatFlow;
// window.PlanDisplay class export removed to avoid overriding instance
window.exportPlan = exportPlan; 