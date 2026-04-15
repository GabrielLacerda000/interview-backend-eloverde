<?php

namespace App\Domain\Waste\Actions\CollectTask;

use App\Domain\Waste\DTO\CollectTask\PreExecutionCheckResultDTO;
use App\Domain\Waste\Models\CollectTask;
use Illuminate\Support\Collection;

class PreExecutionCheckAction
{
    public static function execute(Collection $collectTaskIds): Collection
    {
        return CollectTask::query()
            ->with(['items', 'wasteGenerationPoint.documents.documentType'])
            ->whereIn('id', $collectTaskIds)
            ->get()
            ->sortByDesc('is_urgent')
            ->values()
            ->map(function (CollectTask $collectTask): PreExecutionCheckResultDTO {
                return new PreExecutionCheckResultDTO(
                    collect_task_id: $collectTask->id,
                    can_execute: true,
                    priority: $collectTask->is_urgent ? 'high' : 'normal',
                    blockers: [],
                    suggested_action: 'execute',
                    related_collect_task_id: null,
                );
            });
    }
}
