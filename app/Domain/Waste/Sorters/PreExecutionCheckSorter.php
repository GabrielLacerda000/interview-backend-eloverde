<?php

namespace App\Domain\Waste\Sorters;

use App\Domain\Waste\DTO\CollectTask\PreExecutionCheckResultDTO;
use Illuminate\Support\Collection;

class PreExecutionCheckSorter
{
    /**
     * @param Collection<PreExecutionCheckResultDTO> $results
     * @return Collection<PreExecutionCheckResultDTO>
     */
    public function sort(Collection $results): Collection
    {
        // 'high' < 'normal' alphabetically, so ASC puts urgent first
        return $results
            ->sortBy([
                fn ($a, $b) => $a->priority <=> $b->priority,
                fn ($a, $b) => $a->can_execute <=> $b->can_execute,
                fn ($a, $b) => $a->scheduled_to <=> $b->scheduled_to,
            ])
            ->values();
    }
}
