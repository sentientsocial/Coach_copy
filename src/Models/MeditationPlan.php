<?php

namespace SentientCoach\Models;

use DateTime;

class MeditationPlan
{
    public function __construct(
        public string $title,
        public string $overview,
        public array $schedule, // Array of MeditationDay
        public string $weeklyReflection,
        public array $successTips,
        public array $trustedResources = [],
        public ?DateTime $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTime();
    }
    
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'overview' => $this->overview,
            'schedule' => array_map(fn($day) => $day->toArray(), $this->schedule),
            'weeklyReflection' => $this->weeklyReflection,
            'successTips' => $this->successTips,
            'trustedResources' => $this->trustedResources,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
    
    public static function fromArray(array $data): self
    {
        $schedule = array_map(
            fn($dayData) => MeditationDay::fromArray($dayData),
            $data['schedule']
        );
        
        return new self(
            $data['title'],
            $data['overview'],
            $schedule,
            $data['weeklyReflection'],
            $data['successTips'],
            $data['trustedResources'] ?? [],
            isset($data['createdAt']) ? new DateTime($data['createdAt']) : null
        );
    }
} 