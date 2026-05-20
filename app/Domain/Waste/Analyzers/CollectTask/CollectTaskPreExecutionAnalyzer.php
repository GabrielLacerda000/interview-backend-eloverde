<?php

namespace App\Domain\Waste\Analyzers\CollectTask;

use App\Domain\Waste\Contracts\CollectTaskCheckerInterface;
use App\Domain\Waste\DTO\CollectTask\PreExecutionCheckResultDTO;
use App\Domain\Waste\Enums\Blocker;
use App\Domain\Waste\Models\CollectTask;

class CollectTaskPreExecutionAnalyzer
{
    /**
     * @param CollectTaskCheckerInterface[] $checkers
     */
    public function __construct(private readonly array $checkers) {}

    public function analyze(CollectTask $task): PreExecutionCheckResultDTO
    {
        $blockers = [];
        $relatedCollectTaskId = null;

        foreach ($this->checkers as $checker) {
            $result = $checker->check($task);

            array_push($blockers, ...$result->blockers);

            if ($result->relatedCollectTaskId !== null) {
                $relatedCollectTaskId = $result->relatedCollectTaskId;
            }
        }

        $blockerValues = array_map(fn(Blocker $b) => $b->value, $blockers);

        return new PreExecutionCheckResultDTO(
            collect_task_id: $task->id,
            can_execute: empty($blockers),
            priority: $task->is_urgent ? "high" : "normal",
            blockers: $blockerValues,
            suggested_action: $this->resolveSuggestedAction($blockers),
            related_collect_task_id: $relatedCollectTaskId,
            scheduled_to: $task->scheduled_to,
        );
    }

    /**
     * @param Blocker[] $blockers
     */
    private function resolveSuggestedAction(array $blockers): string
    {
        $documentBlockers = [
            Blocker::MISSING_REQUIRED_DOCUMENTS,
            Blocker::EXPIRED_REQUIRED_DOCUMENTS,
            Blocker::INVALID_REQUIRED_DOCUMENTS,
        ];

        $types = array_unique($blockers, SORT_REGULAR);

        return match (true) {
            empty($blockers) => "execute",
            $types === [Blocker::INVALID_STATE] => "fix_state",
            $types === [Blocker::DUPLICATE_COLLECT_FOR_SAME_DAY]
                => "review_or_merge",
            empty(
                array_filter(
                    $types,
                    fn(Blocker $b) => !in_array($b, $documentBlockers, true),
                )
            )
                => "review_documents",
            default => "manual_review",
        };
    }
}
