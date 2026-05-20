<?php

namespace App\Domain\Waste\DTO\CollectTask;

use App\Domain\Waste\Enums\Blocker;

class CheckResult
{
    /**
     * @param Blocker[] $blockers
     */
    public function __construct(
        public readonly array $blockers,
        public readonly ?int $relatedCollectTaskId = null,
    ) {
    }
}
