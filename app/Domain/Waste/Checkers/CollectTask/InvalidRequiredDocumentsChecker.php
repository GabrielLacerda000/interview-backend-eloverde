<?php

namespace App\Domain\Waste\Checkers\CollectTask;

use App\Domain\Document\Models\Document;
use App\Domain\Waste\Contracts\CollectTaskCheckerInterface;
use App\Domain\Waste\DTO\CollectTask\CheckResult;
use App\Domain\Waste\Enums\Blocker;
use App\Domain\Waste\Models\CollectTask;

class InvalidRequiredDocumentsChecker implements CollectTaskCheckerInterface
{
    public function check(CollectTask $task): CheckResult
    {
        $hasInvalid = $task->wasteGenerationPoint
            ->documents
            ->filter(fn ($doc) => $doc->documentType->is_required)
            ->filter(fn ($doc) => $doc->status === Document::STATUS_INVALID)
            ->isNotEmpty();

        if ($hasInvalid) {
            return new CheckResult([Blocker::INVALID_REQUIRED_DOCUMENTS]);
        }

        return new CheckResult([]);
    }
}
