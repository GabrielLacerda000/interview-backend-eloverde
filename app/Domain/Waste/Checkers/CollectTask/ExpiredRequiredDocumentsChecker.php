<?php

namespace App\Domain\Waste\Checkers\CollectTask;

use App\Domain\Waste\Contracts\CollectTaskCheckerInterface;
use App\Domain\Waste\DTO\CollectTask\CheckResult;
use App\Domain\Waste\Enums\Blocker;
use App\Domain\Waste\Models\CollectTask;

class ExpiredRequiredDocumentsChecker implements CollectTaskCheckerInterface
{
    public function check(CollectTask $task): CheckResult
    {
        $hasExpired = $task->wasteGenerationPoint
            ->documents
            ->filter(fn ($doc) => $doc->documentType->is_required)
            ->filter(fn ($doc) => $doc->expires_at !== null && $doc->expires_at->isPast())
            ->isNotEmpty();

        if ($hasExpired) {
            return new CheckResult([Blocker::EXPIRED_REQUIRED_DOCUMENTS]);
        }

        return new CheckResult([]);
    }
}
