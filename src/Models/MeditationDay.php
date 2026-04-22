<?php

namespace SentientCoach\Models;

class MeditationDay
{
    public function __construct(
        public string $day,
        public string $practice,
        public string $duration,
        public string $description,
        public string $instructions,
        public string $coachingNotes,
        public array $recommendedResources = []
    ) {}
    
    public function toArray(): array
    {
        return [
            'day' => $this->day,
            'practice' => $this->practice,
            'duration' => $this->duration,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'coachingNotes' => $this->coachingNotes,
            'recommendedResources' => $this->recommendedResources
        ];
    }
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['day'],
            $data['practice'],
            $data['duration'],
            $data['description'],
            $data['instructions'],
            $data['coachingNotes'],
            $data['recommendedResources'] ?? []
        );
    }
} 