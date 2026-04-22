<!-- Predefined Questionnaire Flow Container -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-slate-200/50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-teal-600/10 rounded-xl border border-teal-200/20 flex items-center justify-center">
                <span class="text-lg">📝</span>
            </div>
            <div>
                <h2 class="font-bold text-lg text-slate-800">Quick Questionnaire</h2>
                <p class="text-xs text-teal-600 font-medium">2-Minute Assessment</p>
            </div>
        </div>
        <div class="text-sm text-slate-500" id="progress-text">
            Question 1 of 8
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="px-6 mb-8">
        <div class="flex justify-between text-xs text-slate-500 mb-2">
            <span>Building your perfect plan...</span>
            <span id="progress-percent">0%</span>
        </div>
        <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-500 transition-all duration-500 ease-out" 
                 id="progress-bar" style="width: 0%"></div>
        </div>
    </div>

    <!-- Question Container -->
    <div class="max-w-2xl mx-auto px-6 pb-12">
        <div id="question-container" class="space-y-6">
            <!-- Question Display -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
                <h3 class="text-xl font-semibold text-slate-900 mb-4" id="question-title">
                    <!-- Question will be inserted here -->
                </h3>
                
                <!-- Options Container -->
                <div class="space-y-3" id="options-container">
                    <!-- Options will be inserted here -->
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="button" id="back-button"
                        class="flex sm:w-auto px-6 py-3 border border-slate-300 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 transition-all duration-200 hidden">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        <span>Back</span>
                    </div>
                </button>
                
                <button type="button" id="next-button"
                        class="flex-1 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02]">
                    <div class="flex items-center justify-center gap-2">
                        <span id="next-text">Continue</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </div>
                </button>
            </div>
        </div>

        <!-- Completion State -->
        <div id="completion-state" class="hidden space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-green-800">
                        Perfect! All questions completed. 🎉
                    </h3>
                </div>
                <p class="text-green-700 mb-6">
                    Thank you for completing the questionnaire. I'll now create a personalized meditation plan based on your answers.
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
    <div class="bg-white rounded-xl p-6 max-w-md w-full animate-fade-in-up">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-900">Oops! Something went wrong</h3>
        </div>
        <p class="text-slate-600 mb-6" id="error-message">
            I encountered an issue while processing your request. Let's try that again.
        </p>
        <div class="flex gap-3">
            <button type="button" id="retry-button"
                    class="flex-1 bg-teal-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-teal-700 transition-colors">
                Try Again
            </button>
            <button type="button" id="close-error"
                    class="flex-1 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-200 transition-colors">
                Close
            </button>
        </div>
    </div>
</div> 