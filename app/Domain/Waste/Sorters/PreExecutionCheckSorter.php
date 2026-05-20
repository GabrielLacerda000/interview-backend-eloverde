<?php

namespace App\Domain\Waste\Sorters;

use App\Domain\Waste\DTO\CollectTask\PreExecutionCheckResultDTO;
use Illuminate\Support\Collection;

class PreExecutionCheckSorter
{
    private const PRIORITY_WEIGHTS = [
        "high" => 1,
        "normal" => 2,
        "low" => 3,
    ];

    /**
     * @param Collection<PreExecutionCheckResultDTO> $results
     * @return Collection<PreExecutionCheckResultDTO>
     */
    public function sort(Collection $results): Collection
    {
        return $results
            ->sortBy([
                fn($a, $b) => self::PRIORITY_WEIGHTS[$a->priority] <=>
                    self::PRIORITY_WEIGHTS[$b->priority],

                fn($a, $b) => $a->can_execute <=> $b->can_execute,

                fn($a, $b) => $a->scheduled_to <=> $b->scheduled_to,
            ])
            ->values();
    }
}
