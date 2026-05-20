<?php

namespace App\Domain\Waste\Checkers\CollectTask;

use App\Domain\Waste\Contracts\CollectTaskCheckerInterface;
use App\Domain\Waste\DTO\CollectTask\CheckResult;
use App\Domain\Waste\Enums\Blocker;
use App\Domain\Waste\Models\CollectTask;

class InvalidStateChecker implements CollectTaskCheckerInterface
{
    public function check(CollectTask $task): CheckResult
    {
        if ($task->state !== CollectTask::STATE_PROGRAMMING) {
            return new CheckResult([Blocker::INVALID_STATE]);
        }

        return new CheckResult([]);
    }
}
