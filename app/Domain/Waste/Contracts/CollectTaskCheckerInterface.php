<?php

namespace App\Domain\Waste\Contracts;

use App\Domain\Waste\DTO\CollectTask\CheckResult;
use App\Domain\Waste\Models\CollectTask;

interface CollectTaskCheckerInterface
{
    public function check(CollectTask $task): CheckResult;
}
