<!-- Chat-style Questions Flow Container -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-teal-600/10 rounded-xl border border-teal-200/20 dark:border-teal-700/30 flex items-center justify-center">
                <span class="text-lg">🧘‍♀️</span>
            </div>
            <div>
                <h2 class="font-bold text-lg text-slate-800 dark:text-slate-100">AI Coach</h2>
                <p class="text-xs text-teal-600 dark:text-teal-400 font-medium">Your Meditation Guide</p>
            </div>
        </div>
        <div class="text-sm text-slate-500 dark:text-slate-400" id="progress-text">
            Question 1 of 8
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="px-6 mb-8">
        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-2">
            <span>Building your perfect plan...</span>
            <span id="progress-percent">0%</span>
        </div>
        <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 transition-all duration-500 ease-out" 
                 id="progress-bar" style="width: 0%"></div>
        </div>
    </div>

    <!-- Chat Container -->
    <div class="max-w-2xl mx-auto px-6 pb-12">
        <!-- Loading State -->
        <div id="loading-state" class="space-y-4">
            <!-- AI Message Bubble -->
            <div class="flex items-start gap-3 animate-fade-in-up">
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1 max-w-lg">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl rounded-tl-md p-4 shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></div>
                            <div class="w-2 h-2 bg-teal-400 rounded-full animate-pulse" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-teal-300 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300" id="loading-message">
                            Analyzing your unique situation...
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Display -->
        <div id="question-container" class="hidden space-y-6">
            <!-- AI Question Message -->
            <div class="flex items-start gap-3 animate-fade-in-up">
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm">🤖</span>
                </div>
                <div class="flex-1 max-w-lg">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl rounded-tl-md p-4 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h3 class="font-semibold text-slate-900 dark:text-white mb-2" id="question-title">
                            <!-- Question will be inserted here -->
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300" id="question-subtitle">
                            <!-- Subtitle will be inserted here -->
                        </p>
                    </div>
                </div>
            </div>

            <!-- Answer Options -->
            <div class="space-y-3" id="options-container">
                <!-- Options will be inserted here -->
            </div>

            <!-- Custom Input Section -->
            <div id="custom-input-section" class="space-y-4">
                <!-- Custom Toggle Button -->
                <button type="button" id="custom-toggle" 
                        class="w-full py-4 border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-teal-300 dark:hover:border-teal-600 transition-colors rounded-xl group">
                    <div class="flex items-center justify-center gap-2 text-slate-600 dark:text-slate-400 group-hover:text-teal-600 dark:group-hover:text-teal-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-sm font-medium">Write your own answer</span>
                    </div>
                </button>

                <!-- Custom Input Area -->
                <div id="custom-input-area" class="hidden">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-slate-600 dark:text-slate-300 text-sm">👤</span>
                        </div>
                        <div class="flex-1">
                            <div class="bg-teal-50 dark:bg-teal-900/20 border-2 border-teal-200 dark:border-teal-700 rounded-2xl rounded-tl-md p-4">
                                <textarea id="custom-answer" rows="3" 
                                          class="w-full border-0 bg-transparent focus:ring-0 resize-none placeholder:text-teal-600/60 dark:placeholder:text-teal-400/60 text-slate-700 dark:text-slate-300"
                                          placeholder="Tell me in your own words..."></textarea>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-teal-600/70 dark:text-teal-400/70">Your personal response</span>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="document.getElementById('custom-answer').value = ''; document.getElementById('custom-input-area').classList.add('hidden'); if(window.QuestionFlow) window.QuestionFlow.updateSubmitButton();"
                                                class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="button" id="submit-answer"
                        class="flex-1 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02]">
                    <div class="flex items-center justify-center gap-2">
                        <span id="submit-text">Continue</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </div>
                </button>
                
                <button type="button" id="back-button"
                        class="sm:w-auto px-6 py-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 hidden">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        <span>Back</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Completion State -->
        <div id="completion-state" class="hidden space-y-6">
            <!-- AI Completion Message -->
            <div class="flex items-start gap-3 animate-fade-in-up">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1 max-w-lg">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl rounded-tl-md p-4">
                        <h3 class="font-semibold text-green-800 dark:text-green-200 mb-2">
                            Perfect! I have everything I need. 🎉
                        </h3>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-4">
                            Thank you for sharing your thoughts with me. Now I'll create a personalized meditation plan just for you based on your unique situation and goals.
                        </p>
                        <button type="button" id="generate-plan"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                            <span id="generate-text">Create My Plan</span>
                            <div id="generate-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-12 text-center">
            <form method="POST" action="/reset" class="inline">
                <button type="submit" class="text-slate-500 hover:text-slate-700 text-sm transition-colors bg-transparent border-none cursor-pointer">
                    ← Back to home
                </button>
            </form>    
       
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="error-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 max-w-md w-full animate-fade-in-up">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Oops! Something went wrong</h3>
        </div>
        <p class="text-slate-600 dark:text-slate-300 mb-6" id="error-message">
            I encountered an issue while processing your request. Let's try that again.
        </p>
        <div class="flex gap-3">
            <button type="button" id="retry-button"
                    class="flex-1 bg-teal-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-teal-700 transition-colors">
                Try Again
            </button>
            <button type="button" id="close-error"
                    class="flex-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-lg font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

 