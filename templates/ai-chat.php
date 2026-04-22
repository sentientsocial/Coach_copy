<!-- AI Chatbot Flow Container -->
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-slate-200/50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-teal-600/10 rounded-xl border border-teal-200/20 flex items-center justify-center">
                <span class="text-lg">🤖</span>
            </div>
            <div>
                <h2 class="font-bold text-lg text-slate-800">AI Coach</h2>
                <p class="text-xs text-teal-600 font-medium">Your Personal Meditation Guide</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span class="text-sm text-green-600 font-medium">Online</span>
        </div>
    </div>

    <!-- Chat Container -->
    <div class="flex flex-col h-[calc(100vh-88px)]">
        <!-- Messages Container -->
        <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 max-w-4xl mx-auto w-full">
            <!-- Welcome Message -->
            <div class="flex items-start gap-3 animate-fade-in-up">
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm">🤖</span>
                </div>
                <div class="flex-1 max-w-lg">
                    <div class="bg-white rounded-2xl rounded-tl-md p-4 shadow-sm border border-slate-200">
                        <p class="text-slate-700">
                            Hello! I'm your AI meditation coach. 🧘‍♀️ I'll ask you a few questions to understand your needs and create a personalized meditation plan. 
                        </p>
                        <p class="text-slate-700 mt-2">
                            Feel free to answer in your own words - I'm here to listen and understand what works best for you.
                        </p>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 ml-2">AI Coach • Just now</div>
                </div>
            </div>
        </div>

        <!-- Input Container -->
        <div class="border-t border-slate-200 bg-white/50 backdrop-blur-sm">
            <div class="max-w-4xl mx-auto p-6">
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <div class="relative">
                            <textarea id="message-input" 
                                    rows="1" 
                                    placeholder="Type your answer here..."
                                    class="w-full min-h-[44px] max-h-32 px-4 py-3 pr-12 border border-slate-300 rounded-xl resize-none focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white text-slate-900 placeholder:text-slate-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled></textarea>
                            <button id="send-button" 
                                    type="button" 
                                    disabled
                                    class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-teal-600 hover:bg-teal-700 disabled:bg-slate-400 disabled:cursor-not-allowed text-white rounded-lg flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div id="quick-actions" class="mt-3 flex flex-wrap gap-2">
                    <!-- Quick action buttons will be populated here -->
                </div>

                <!-- Status Indicator -->
                <div class="mt-3 text-center">
                    <div id="typing-indicator" class="hidden flex items-center justify-center gap-2 text-sm text-slate-500">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-teal-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                        <span>AI Coach is thinking...</span>
                    </div>
                    <div id="progress-indicator" class="hidden text-sm text-teal-600">
                        Question <span id="current-question">1</span> of 7
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="fixed bottom-6 left-6">
        <form method="POST" action="/reset" class="inline">
            <button type="submit" class="text-slate-500 hover:text-slate-700 text-sm transition-colors bg-transparent border-none cursor-pointer">
                ← Back to home
            </button>
        </form>    
    </div>
</div>

<!-- Completion Modal -->
<div id="completion-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full animate-fade-in-up">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 mb-2">
                Perfect! 🎉
            </h3>
            <p class="text-slate-600 mb-6">
                I have all the information I need to create your personalized meditation plan.
            </p>
            <button type="button" id="create-plan-button"
                    class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02]">
                <div class="flex items-center justify-center gap-2">
                    <span id="create-plan-text">Create My Plan</span>
                    <div id="create-plan-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </div>
            </button>
        </div>
    </div>
</div> 