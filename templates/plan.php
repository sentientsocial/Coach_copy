<!-- Plan Display Container -->
<?php
// DEBUG: Add debugging info to the plan template
$debugFile = __DIR__ . '/../logs/debug.log';
file_put_contents($debugFile, "\n=== PLAN TEMPLATE DEBUG ===" . PHP_EOL, FILE_APPEND);
file_put_contents($debugFile, 'plan_isset=' . (isset($plan) ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
file_put_contents($debugFile, 'plan_is_null=' . ($plan === null ? 'yes' : 'no') . PHP_EOL, FILE_APPEND);
if (isset($plan) && $plan !== null) {
    file_put_contents($debugFile, 'plan_title=' . ($plan->title ?? 'no title') . PHP_EOL, FILE_APPEND);
    file_put_contents($debugFile, 'plan_class=' . get_class($plan) . PHP_EOL, FILE_APPEND);
}
?>
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100" <?php if (!isset($plan) || $plan === null): ?>style="display: none;"<?php else: ?>data-plan-exists="true"<?php endif; ?>>
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-slate-200/50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-teal-600/10 rounded-xl border border-teal-200/20 flex items-center justify-center">
                <span class="text-lg">🎯</span>
            </div>
            <div>
                <h2 class="font-bold text-lg text-slate-800">Your Meditation Plan</h2>
                <p class="text-xs text-teal-600 font-medium">Personalized by AI</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- <button type="button" id="feedback-button"
                    class="inline-flex items-center gap-2 bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Let's refine
            </button> -->
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-8">
        <!-- Plan Introduction -->
        <div class="mb-8">
            <div class="flex items-start gap-3 animate-fade-in-up">
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm">🤖</span>
                </div>
                <div class="flex-1 max-w-3xl">
                    <div class="bg-white rounded-2xl rounded-tl-md p-6 shadow-sm border border-slate-200">
                        <h1 class="text-2xl font-bold text-slate-900" id="plan-title">
                            <!-- Plan title will be loaded here -->
                        </h1>
                        <p class="text-slate-600 leading-relaxed" id="plan-overview">
                            <!-- Plan overview will be loaded here -->
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <!-- 7-Day Schedule -->
        <div class="space-y-6 mb-8" id="schedule-container">
            <!-- Days will be loaded here -->
        </div>

        <!-- Trusted Resources -->
        <!-- <div class="flex items-start gap-3 mb-8" id="trusted-resources-section" style="display: none;">
            <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-sm">🤖</span>
            </div>
            <div class="flex-1 max-w-3xl">
                <div class="bg-white rounded-2xl rounded-tl-md p-6 shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        🔗 Trusted Resources
                    </h3>
                    <p class="text-slate-600 mb-4 text-sm">
                        Based on your preferences, here are curated meditation resources to support your practice:
                    </p>
                    <div class="grid gap-4" id="trusted-resources">
                       
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Plan Customization Section -->
        <div class="mb-8">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-300 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-slate-600 text-sm">👤</span>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-2xl rounded-tl-md p-6 shadow-sm border border-slate-200">
                        <p class="text-slate-700 font-medium mb-4">Is there anything you'd like to modify about your program? Or perhaps you'd like to chat about other things related to meditation?</p>
                        <button type="button" id="feedback-button"
                                class="inline-flex items-center gap-2 bg-teal-600 text-white px-6 py-3 rounded-lg hover:bg-teal-700 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Let's Customize
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="mb-8">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-slate-300 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-slate-600 text-sm">👤</span>
                </div>
                <div class="flex-1">
                    <div class="bg-slate-100 rounded-2xl rounded-tl-md p-4 border border-slate-200">
                        <p class="text-slate-700 font-medium mb-3">I'd like to download this plan:</p>
                        <div class="grid grid-cols-2 gap-3 max-w-md">
                            <button type="button" onclick="exportPlan('calendar')" 
                                    class="flex flex-col items-center gap-3 p-4 bg-white rounded-lg hover:bg-slate-50 transition-colors group border border-slate-300">
                                <svg class="w-6 h-6 text-slate-600 group-hover:text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-slate-700">Calendar</span>
                                <span class="text-xs text-slate-500">With trusted resources</span>
                            </button>
                            <button type="button" onclick="exportPlan('html')" 
                                    class="flex flex-col items-center gap-3 p-4 bg-white rounded-lg hover:bg-slate-50 transition-colors group border border-slate-300">
                                <svg class="w-6 h-6 text-slate-600 group-hover:text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-slate-700">HTML</span>
                                <span class="text-xs text-slate-500">With trusted resources</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Weekly Reflection & Tips -->
        <div class="space-y-8">
            <!-- Weekly Reflection -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm">🤖</span>
                </div>
                <div class="flex-1 max-w-3xl">
                    <div class="bg-white rounded-2xl rounded-tl-md p-6 shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                            💭 Weekly Reflection
                        </h3>
                        <p class="text-slate-600 leading-relaxed" id="weekly-reflection">
                            <!-- Reflection content will be loaded here -->
                        </p>
                    </div>
                </div>
            </div>

            <!-- Success Tips -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm">🤖</span>
                </div>
                <div class="flex-1 max-w-3xl">
                    <div class="bg-white rounded-2xl rounded-tl-md p-6 shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            ✨ Success Tips
                        </h3>
                        <ul class="space-y-3" id="success-tips">
                            <!-- Tips will be loaded here -->
                        </ul>
                    </div>
                </div>
            </div>
            
            
        </div>

        <!-- Navigation -->
        <div class="mt-12 text-center space-x-6">
            <form method="POST" action="/reset" class="inline">
                <button type="submit" class="text-slate-500 hover:text-slate-700 text-sm transition-colors bg-transparent border-none cursor-pointer">
                    ← Retake questionnaire
                </button>
            </form>
            <form method="POST" action="/reset" class="inline">
                <button type="submit" class="text-slate-500 hover:text-slate-700 text-sm transition-colors bg-transparent border-none cursor-pointer">
                    Start over
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div id="feedback-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-900">Customize Your Plan</h3>
            <button type="button" id="close-feedback" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <p class="text-slate-600 mb-4">
            Tell us what you'd like to adjust about your meditation plan. For example:
        </p>
        
        <ul class="text-sm text-slate-500 mb-6 space-y-1">
            <li>• "I can't do morning meditation on weekdays"</li>
            <li>• "I prefer shorter sessions"</li>
            <li>• "I need more breathing exercises"</li>
            <li>• "The evening sessions are too long"</li>
        </ul>
        
        <textarea id="feedback-text" rows="4" 
                  class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none mb-6"
                  placeholder="What would you like to change about your plan?"></textarea>
        
        <div class="flex gap-3">
            <button type="button" id="submit-feedback"
                    class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors disabled:opacity-50">
                <span id="feedback-submit-text">Update Plan</span>
                <div id="feedback-spinner" class="hidden ml-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin inline-block"></div>
            </button>
            <button type="button" id="cancel-feedback"
                    class="px-6 py-3 border border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 focus:outline-none transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Loading State -->
<div id="plan-loading" class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100" <?php if (isset($plan) && $plan !== null): ?>style="display: none;"<?php endif; ?>>
    <div class="text-center">
        <div class="animate-spin w-12 h-12 border-4 border-teal-600 border-t-transparent rounded-full mx-auto mb-4"></div>
        <p class="text-slate-600 text-lg">Creating your personalized meditation plan...</p>
        <p class="text-slate-500 text-sm mt-2">This will just take a moment</p>
    </div>
</div>

<script>
// Initialize plan display when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!isset($plan) || $plan === null): ?>
    // No plan exists, auto-generate it
    autoGeneratePlan();
    <?php else: ?>
    // Plan exists, initialize display
    if (typeof window.PlanDisplay !== 'undefined') {
        window.PlanDisplay.init();
    }
    <?php endif; ?>
});

// Auto-generate plan function
async function autoGeneratePlan() {
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

        // Reload the page to show the generated plan
        window.location.reload();
        
    } catch (error) {
        console.error('Error generating plan:', error);
        // Redirect to questions page on error
        window.location.href = '/questions';
    }
}

// Export function
function exportPlan(format) {
    window.location.href = `/api/export/${format}`;
}
</script> 