<?php

namespace App\Domain\Waste\DTO\CollectTask;

use Carbon\Carbon;

class PreExecutionCheckResultDTO
{
    public function __construct(
        public int $collect_task_id,
        public bool $can_execute,
        public string $priority,
        public array $blockers,
        public string $suggested_action,
        public ?int $related_collect_task_id,
        public Carbon $scheduled_to,
    ) {
    }

    public function toArray(): array
    {
        return [
            'collect_task_id' => $this->collect_task_id,
            'can_execute' => $this->can_execute,
            'priority' => $this->priority,
            'blockers' => $this->blockers,
            'suggested_action' => $this->suggested_action,
            'related_collect_task_id' => $this->related_collect_task_id,
        ];
    }
}
