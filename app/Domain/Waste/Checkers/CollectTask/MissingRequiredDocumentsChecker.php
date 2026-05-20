<?php

namespace App\Domain\Waste\Checkers\CollectTask;

use App\Domain\Document\Models\DocumentType;
use App\Domain\Waste\Contracts\CollectTaskCheckerInterface;
use App\Domain\Waste\DTO\CollectTask\CheckResult;
use App\Domain\Waste\Enums\Blocker;
use App\Domain\Waste\Models\CollectTask;

class MissingRequiredDocumentsChecker implements CollectTaskCheckerInterface
{
    public function check(CollectTask $task): CheckResult
    {
        $requiredTypeIds = DocumentType::where('is_required', true)->pluck('id');
        $existingTypeIds = $task->wasteGenerationPoint->documents->pluck('document_type_id');
        $missing = $requiredTypeIds->diff($existingTypeIds);

        if ($missing->isNotEmpty()) {
            return new CheckResult([Blocker::MISSING_REQUIRED_DOCUMENTS]);
        }

        return new CheckResult([]);
    }
}
