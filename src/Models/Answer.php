<?php

namespace SentientCoach\Models;

use DateTime;

class Answer
{
    public function __construct(
        public int $questionNumber,
        public string $question,
        public string $answer,
        public bool $isCustom,
        public ?string $optionId = null,
        public ?DateTime $createdAt = null
    ) {
        $this->createdAt = $createdAt ?? new DateTime();
    }
    
    public function toArray(): array
    {
        return [
            'questionNumber' => $this->questionNumber,
            'question' => $this->question,
            'answer' => $this->answer,
            'isCustom' => $this->isCustom,
            'optionId' => $this->optionId,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['questionNumber'],
            $data['question'],
            $data['answer'],
            $data['isCustom'],
            $data['optionId'] ?? null,
            isset($data['createdAt']) ? new DateTime($data['createdAt']) : null
        );
    }
} 