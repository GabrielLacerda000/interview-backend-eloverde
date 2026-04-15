<?php

namespace App\Domain\Waste\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreExecutionCheckResource extends JsonResource
{
    public function toArray(Request $request): array
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
