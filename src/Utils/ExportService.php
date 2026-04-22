<?php

namespace SentientCoach\Utils;

use SentientCoach\Models\MeditationPlan;

class ExportService
{
    public function exportToCalendar(MeditationPlan $plan): string
    {
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Sentient AI Coach//Meditation Plan//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        
        $startDate = new \DateTime('next monday');
        
        foreach ($plan->schedule as $index => $day) {
            $eventDate = clone $startDate;
            $eventDate->add(new \DateInterval('P' . $index . 'D'));
            
            // Build event description with meditation details
            $description = $day->description . "\n\n" . $day->instructions;
            
            // Add coaching notes if available
            if (!empty($day->coachingNotes)) {
                $description .= "\n\nCoaching Notes: " . $day->coachingNotes;
            }
            
            // Add recommended resources if available
            if (!empty($day->recommendedResources)) {
                $description .= "\n\nRecommended Resources:";
                foreach ($day->recommendedResources as $resource) {
                    $description .= "\n• " . $resource['name'] . " (" . $resource['type'] . ")";
                    $description .= "\n  " . $resource['reason'];
                    $description .= "\n  " . $resource['specificContent'];
                    if (!empty($resource['link'])) {
                        $description .= "\n  Link: " . $resource['link'];
                    }
                    $description .= "\n";
                }
            }
            
            // Add recommended resources if available
            if (!empty($day->recommendedResources)) {
                $description .= "\n\nRecommended Resources:";
                foreach ($day->recommendedResources as $resource) {
                    $description .= "\n• " . $resource['name'] . " (" . $resource['type'] . ")";
                    $description .= "\n  " . $resource['reason'];
                    $description .= "\n  " . $resource['specificContent'];
                    if (!empty($resource['link'])) {
                        $description .= "\n  Link: " . $resource['link'];
                    }
                    $description .= "\n";
                }
            }
            
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:" . uniqid() . "@sentientcoach.com\r\n";
            $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $ics .= "DTSTART:" . $eventDate->format('Ymd\T080000') . "\r\n";
            $ics .= "DTEND:" . $eventDate->format('Ymd\T081500') . "\r\n";
            $ics .= "SUMMARY:Meditation: " . $day->practice . "\r\n";
            $ics .= "DESCRIPTION:" . $this->escapeIcsText($description) . "\r\n";
            $ics .= "CATEGORIES:Meditation,Wellness\r\n";
            $ics .= "END:VEVENT\r\n";
        }
        
        // Add trusted resources as a separate informational event (if available)
        if (!empty($plan->trustedResources)) {
            $resourcesDate = clone $startDate;
            $resourcesDate->sub(new \DateInterval('P1D')); // Day before the meditation plan starts
            
            $resourcesDescription = "Recommended meditation resources to support your practice:\n\n";
            foreach ($plan->trustedResources as $resource) {
                $resourcesDescription .= "• " . $resource['name'] . " (" . $resource['type'] . ")\n";
                $resourcesDescription .= "  " . $resource['description'] . "\n";
                $resourcesDescription .= "  Website: " . $resource['website'] . "\n";
                $resourcesDescription .= "  Platforms: " . implode(', ', $resource['platforms']) . "\n\n";
            }
            
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:" . uniqid() . "-resources@sentientcoach.com\r\n";
            $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $ics .= "DTSTART:" . $resourcesDate->format('Ymd') . "\r\n";
            $ics .= "DTEND:" . $resourcesDate->format('Ymd') . "\r\n";
            $ics .= "SUMMARY:📚 Meditation Resources - " . $plan->title . "\r\n";
            $ics .= "DESCRIPTION:" . $this->escapeIcsText($resourcesDescription) . "\r\n";
            $ics .= "CATEGORIES:Meditation,Wellness,Resources\r\n";
            $ics .= "END:VEVENT\r\n";
        }
        
        $ics .= "END:VCALENDAR\r\n";
        
        return $ics;
    }
    
    public function exportToHtml(MeditationPlan $plan): string
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($plan->title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { color: #0d9488; font-size: 2em; margin-bottom: 10px; }
        .overview { font-style: italic; color: #666; margin-bottom: 30px; }
        .day { margin-bottom: 30px; border-left: 4px solid #0d9488; padding-left: 20px; }
        .day-title { font-size: 1.5em; color: #0d9488; margin-bottom: 10px; }
        .practice { font-weight: bold; color: #333; }
        .duration { color: #059669; font-weight: bold; }
        .description { margin: 10px 0; }
        .instructions { background: #f8fafc; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .coaching-notes { font-style: italic; color: #4a5568; }
        .resources { margin: 15px 0; }
        .resources ul { list-style-type: none; padding: 0; margin: 10px 0; }
        .resources li { margin: 8px 0; padding: 8px; background: #f8fafc; border-left: 3px solid #0d9488; }
        .resources a { color: #0d9488; text-decoration: none; }
        .resources a:hover { text-decoration: underline; }
        .reflection, .tips { margin-top: 40px; }
        .tips ul { list-style-type: none; padding: 0; }
        .tips li { background: #f0fdf4; padding: 10px; margin: 5px 0; border-left: 3px solid #059669; }
        @media print { body { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">' . htmlspecialchars($plan->title) . '</h1>
        <p class="overview">' . htmlspecialchars($plan->overview) . '</p>
    </div>
    
    <div class="schedule">';
    
        foreach ($plan->schedule as $day) {
            $html .= '
        <div class="day">
            <h2 class="day-title">' . htmlspecialchars($day->day) . '</h2>
            <p class="practice">' . htmlspecialchars($day->practice) . ' <span class="duration">(' . htmlspecialchars($day->duration) . ')</span></p>
            <p class="description">' . htmlspecialchars($day->description) . '</p>
            <div class="instructions">
                <strong>Instructions:</strong><br>
                ' . nl2br(htmlspecialchars($day->instructions)) . '
            </div>
            <p class="coaching-notes"><em>' . htmlspecialchars($day->coachingNotes) . '</em></p>';
            
            // Add recommended resources if available
            if (!empty($day->recommendedResources)) {
                $html .= '
            <div class="resources">
                <strong>Recommended Resources:</strong>
                <ul>';
                foreach ($day->recommendedResources as $resource) {
                    $resourceName = htmlspecialchars($resource['name']);
                    if (!empty($resource['link'])) {
                        $resourceName = '<a href="' . htmlspecialchars($resource['link']) . '" target="_blank" style="color: #0d9488; text-decoration: none;">' . $resourceName . '</a>';
                    }
                    $html .= '<li style="margin: 8px 0; padding: 8px; background: #f8fafc; border-left: 3px solid #0d9488;">
                        <strong>' . $resourceName . '</strong> (' . htmlspecialchars($resource['type']) . ')<br>
                        <em>' . htmlspecialchars($resource['reason']) . '</em><br>
                        <small>' . htmlspecialchars($resource['specificContent']) . '</small>';
                    
                    if (!empty($resource['link'])) {
                        $html .= '<br><small style="color: #0d9488;"><strong>Link:</strong> <a href="' . htmlspecialchars($resource['link']) . '" target="_blank" style="color: #0d9488;">' . htmlspecialchars($resource['link']) . '</a></small>';
                    }
                    
                    $html .= '</li>';
                }
                $html .= '
                </ul>
            </div>';
            }
            
            $html .= '
        </div>';
        }
        
        $html .= '
    </div>
    
    <div class="reflection">
        <h2>Weekly Reflection</h2>
        <p>' . htmlspecialchars($plan->weeklyReflection) . '</p>
    </div>
    
    <div class="tips">
        <h2>Success Tips</h2>
        <ul>';
        
        foreach ($plan->successTips as $tip) {
            $html .= '<li>' . htmlspecialchars($tip) . '</li>';
        }
        
        $html .= '
        </ul>
    </div>';
    
    // Add trusted resources section if available
    if (!empty($plan->trustedResources)) {
        $html .= '
    
    <div class="resources">
        <h2>Trusted Resources</h2>
        <p style="color: #666; margin-bottom: 20px;">Curated meditation resources to support your practice:</p>';
        
        foreach ($plan->trustedResources as $resource) {
            $platformList = implode(', ', $resource['platforms']);
            $featuresList = '';
            foreach ($resource['features'] as $feature) {
                $featuresList .= '<li style="margin: 5px 0;">' . htmlspecialchars($feature) . '</li>';
            }
            
            $html .= '
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <h3 style="margin: 0; color: #1f2937;">' . htmlspecialchars($resource['name']) . '</h3>
                <span style="background: #0d9488; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8em; font-weight: bold;">' . htmlspecialchars($resource['type']) . '</span>
            </div>
            <p style="color: #4b5563; margin-bottom: 15px;">' . htmlspecialchars($resource['description']) . '</p>
            <div style="margin-bottom: 15px;">
                <strong style="color: #374151;">Key Features:</strong>
                <ul style="margin: 8px 0; padding-left: 20px;">' . $featuresList . '</ul>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #374151; font-size: 0.9em;">Platforms:</strong> 
                    <span style="color: #6b7280;">' . htmlspecialchars($platformList) . '</span>
                </div>
                <a href="https://' . htmlspecialchars($resource['website']) . '" target="_blank" style="color: #0d9488; text-decoration: none; font-weight: bold;">Visit ' . htmlspecialchars($resource['website']) . '</a>
            </div>
        </div>';
        }
        
        $html .= '
    </div>';
    }
    
    $html .= '
</body>
</html>';
        
        return $html;
    }
    
    public function exportToText(MeditationPlan $plan): string
    {
        $text = strtoupper($plan->title) . "\n";
        $text .= str_repeat("=", strlen($plan->title)) . "\n\n";
        
        $text .= "OVERVIEW:\n";
        $text .= $plan->overview . "\n\n";
        
        $text .= "7-DAY MEDITATION SCHEDULE:\n";
        $text .= str_repeat("-", 30) . "\n\n";
        
        foreach ($plan->schedule as $day) {
            $text .= strtoupper($day->day) . "\n";
            $text .= "Practice: " . $day->practice . " (" . $day->duration . ")\n";
            $text .= "Description: " . $day->description . "\n\n";
            $text .= "Instructions:\n" . $day->instructions . "\n\n";
            $text .= "Coaching Notes: " . $day->coachingNotes . "\n";
            $text .= str_repeat("-", 40) . "\n\n";
        }
        
        $text .= "WEEKLY REFLECTION:\n";
        $text .= $plan->weeklyReflection . "\n\n";
        
        $text .= "SUCCESS TIPS:\n";
        foreach ($plan->successTips as $index => $tip) {
            $text .= ($index + 1) . ". " . $tip . "\n";
        }
        
        return $text;
    }
    
    public function exportToJson(MeditationPlan $plan): string
    {
        return json_encode($plan->toArray(), JSON_PRETTY_PRINT);
    }
    
    public function exportResourcesToHtml(MeditationPlan $plan): string
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trusted Meditation Resources</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { color: #0d9488; font-size: 2em; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .resource { margin-bottom: 30px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; }
        .resource-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .resource-name { font-size: 1.3em; font-weight: bold; color: #1f2937; margin: 0; }
        .resource-type { background: #0d9488; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }
        .resource-description { color: #4b5563; margin-bottom: 15px; }
        .features-section { margin-bottom: 15px; }
        .features-title { font-weight: bold; color: #374151; margin-bottom: 8px; }
        .features { list-style: none; padding: 0; margin: 0; }
        .features li { background: #f9fafb; padding: 6px 12px; margin: 4px 0; border-left: 3px solid #0d9488; font-size: 0.9em; color: #6b7280; }
        .resource-footer { display: flex; justify-content: space-between; align-items: center; }
        .platforms { display: flex; gap: 8px; }
        .platform { background: #e5e7eb; color: #374151; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
        .website { color: #0d9488; text-decoration: none; font-weight: bold; }
        .website:hover { text-decoration: underline; }
        @media print { body { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">🔗 Trusted Meditation Resources</h1>
        <p class="subtitle">Curated resources to support your meditation practice</p>
    </div>';

        if (empty($plan->trustedResources)) {
            $html .= '
    <div class="resource">
        <p style="text-align: center; color: #6b7280; font-style: italic;">No specific resources available for your preferences.</p>
    </div>';
        } else {
            foreach ($plan->trustedResources as $resource) {
                $platformBadges = '';
                foreach ($resource['platforms'] as $platform) {
                    $platformBadges .= '<span class="platform">' . htmlspecialchars($platform) . '</span>';
                }
                
                $featuresList = '';
                foreach ($resource['features'] as $feature) {
                    $featuresList .= '<li>' . htmlspecialchars($feature) . '</li>';
                }
                
                $html .= '
    <div class="resource">
        <div class="resource-header">
            <h2 class="resource-name">' . htmlspecialchars($resource['name']) . '</h2>
            <span class="resource-type">' . htmlspecialchars($resource['type']) . '</span>
        </div>
        <p class="resource-description">' . htmlspecialchars($resource['description']) . '</p>
        <div class="features-section">
            <div class="features-title">Key Features:</div>
            <ul class="features">' . $featuresList . '</ul>
        </div>
        <div class="resource-footer">
            <div class="platforms">' . $platformBadges . '</div>
            <a href="https://' . htmlspecialchars($resource['website']) . '" target="_blank" class="website">Visit ' . htmlspecialchars($resource['website']) . '</a>
        </div>
    </div>';
            }
        }

        $html .= '
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #6b7280; font-size: 0.9em;">
        <p>Generated by Sentient AI Coach • ' . date('F j, Y') . '</p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    private function escapeIcsText(string $text): string
    {
        $text = str_replace(["\r\n", "\n", "\r"], "\\n", $text);
        $text = str_replace([",", ";", "\\"], ["\\,", "\\;", "\\\\"], $text);
        return $text;
    }
} 