<?php

namespace SentientCoach\Services;

class ResourceService
{
    private array $guidedResources;
    private array $selfGuidedResources;
    private array $universalResources;
    
    public function __construct()
    {
        $this->loadResources();
    }
    
    public function getResourcesForPreference(string $preference): array
    {
        switch ($preference) {
            case 'Guided meditation':
                return array_merge($this->guidedResources, $this->universalResources);
            case 'Self-guided meditation':
                return array_merge($this->selfGuidedResources, $this->universalResources);
            case 'Both':
                return array_merge($this->guidedResources, $this->selfGuidedResources, $this->universalResources);
            default:
                return $this->universalResources;
        }
    }
    
    private function loadResources(): void
    {
        // Resources specifically for guided meditation preference
        $this->guidedResources = [
            [
                'name' => 'Insight Timer',
                'type' => 'App',
                'description' => 'World\'s largest free meditation library with 130,000+ guided sessions',
                'features' => ['Free access', 'Expert teachers', 'Extensive filters'],
                'website' => 'insighttimer.com',
                'platforms' => ['iOS', 'Android', 'Web']
            ],
            [
                'name' => 'Tara Brach YouTube Channel',
                'type' => 'YouTube',
                'description' => 'Renowned psychologist offering mindfulness, compassion, and emotional healing',
                'features' => ['Free content', 'Expert teacher', 'Accessible approach'],
                'website' => 'youtube.com/user/tarabrach',
                'platforms' => ['Web', 'Mobile']
            ],
            [
                'name' => 'The Mindful Movement',
                'type' => 'YouTube',
                'description' => 'Guided meditations, affirmations, and breathwork for stress and healing',
                'features' => ['Professional quality', 'Vast range of topics', 'Beginner-friendly'],
                'website' => 'youtube.com/c/TheMindfulMovement',
                'platforms' => ['Web', 'Mobile']
            ],
            [
                'name' => 'Healthy Minds Program',
                'type' => 'App',
                'description' => 'Science-based app by Dr. Richard Davidson focusing on awareness, connection, insight',
                'features' => ['Completely free', 'Research-backed', 'Structured curriculum'],
                'website' => 'tryhealthyminds.org',
                'platforms' => ['iOS', 'Android']
            ],
            [
                'name' => 'The Honest Guys',
                'type' => 'YouTube',
                'description' => 'Guided visualizations, sleep meditations, and stress relief',
                'features' => ['Soothing voiceovers', 'High production quality', 'Sleep focus'],
                'website' => 'youtube.com/user/TheHonestGuys',
                'platforms' => ['Web', 'Mobile']
            ]
        ];
        
        // Resources specifically for self-guided meditation preference
        $this->selfGuidedResources = [
            [
                'name' => 'Michael Taft - Deconstructing Yourself',
                'type' => 'YouTube',
                'description' => 'Deep meditation instruction: samatha, jhana, non-dual awareness, neuroscience',
                'features' => ['Advanced techniques', 'Meditation teacher', 'Neuroscience approach'],
                'website' => 'youtube.com/@deconstructingyourself',
                'platforms' => ['Web', 'Mobile']
            ],
            [
                'name' => 'Adyashanti',
                'type' => 'YouTube',
                'description' => 'Non-duality, pure awareness, and spiritual awakening practices',
                'features' => ['Zen tradition', 'Advanced practice', 'Spiritual depth'],
                'website' => 'youtube.com/user/AdyashantiVideo',
                'platforms' => ['Web', 'Mobile']
            ],
            [
                'name' => 'Oak Meditation',
                'type' => 'App',
                'description' => 'Simple, high-quality meditation app with breath, mindfulness, and unguided tracks',
                'features' => ['Minimalist design', 'No ads', 'Timer functions'],
                'website' => 'oakmeditation.com',
                'platforms' => ['iOS']
            ],
            [
                'name' => 'Dharma Seed / AudioDharma',
                'type' => 'Website',
                'description' => 'Vipassana insight meditation and dharma talks from senior teachers',
                'features' => ['Traditional instruction', 'Silent retreat style', 'Deep practice'],
                'website' => 'audiodharma.org',
                'platforms' => ['Web']
            ],
            [
                'name' => 'Yellow Brick Cinema',
                'type' => 'YouTube',
                'description' => 'Background music and ambient soundscapes for meditation',
                'features' => ['Music focus', 'Long sessions', 'Ambient sounds'],
                'website' => 'youtube.com/user/YellowBrickCinema',
                'platforms' => ['Web', 'Mobile']
            ]
        ];
        
        // Universal resources good for both preferences
        $this->universalResources = [
            [
                'name' => 'UCLA Mindful Awareness Research Center (MARC)',
                'type' => 'Website',
                'description' => 'Clinical-grade mindfulness meditations created by researchers and psychologists',
                'features' => ['Evidence-based', 'Clinical applications', 'Multiple languages'],
                'website' => 'uclahealth.org/marc',
                'platforms' => ['Web']
            ],
            [
                'name' => 'Tara Brach & Jack Kornfield Archives',
                'type' => 'Website',
                'description' => 'Free downloads and streaming from renowned Western Buddhist teachers',
                'features' => ['Decades of experience', 'Compassionate guidance', 'Complete practices'],
                'website' => 'tarabrach.com, jackkornfield.com',
                'platforms' => ['Web']
            ],
            [
                'name' => 'Smiling Mind',
                'type' => 'App',
                'description' => 'Australian-made app with tailored content for different age groups and situations',
                'features' => ['Completely free', 'Age-appropriate', 'School programs'],
                'website' => 'smilingmind.com.au',
                'platforms' => ['iOS', 'Android', 'Web']
            ]
        ];
    }
} 