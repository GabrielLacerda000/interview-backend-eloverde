<?php

namespace App\Domain\Waste\Checkers\CollectTask;

use App\Domain\Waste\Contracts\CollectTaskCheckerInterface;
use App\Domain\Waste\DTO\CollectTask\CheckResult;
use App\Domain\Waste\Enums\Blocker;
use App\Domain\Waste\Models\CollectTask;

class DuplicateCollectTaskChecker implements CollectTaskCheckerInterface
{
    public function check(CollectTask $task): CheckResult
    {
        $wasteIds = $task->items->pluck('waste_id')->sort()->values()->all();

        $duplicate = CollectTask::where('waste_generation_point_id', $task->waste_generation_point_id)
            ->whereDate('scheduled_to', $task->scheduled_to->toDateString())
            ->where('id', '!=', $task->id)
            ->with('items')
            ->get()
            ->first(function (CollectTask $other) use ($wasteIds): bool {
                $otherWasteIds = $other->items->pluck('waste_id')->sort()->values()->all();

                return $wasteIds === $otherWasteIds;
            });

        if ($duplicate !== null) {
            return new CheckResult(
                blockers: [Blocker::DUPLICATE_COLLECT_FOR_SAME_DAY],
                relatedCollectTaskId: $duplicate->id,
            );
        }

        return new CheckResult([]);
    }
}
