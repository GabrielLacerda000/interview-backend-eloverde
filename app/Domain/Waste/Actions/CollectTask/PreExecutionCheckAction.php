<?php

namespace App\Domain\Waste\Actions\CollectTask;

use App\Domain\Waste\Analyzers\CollectTask\CollectTaskPreExecutionAnalyzer;
use App\Domain\Waste\Checkers\CollectTask\DuplicateCollectTaskChecker;
use App\Domain\Waste\Checkers\CollectTask\ExpiredRequiredDocumentsChecker;
use App\Domain\Waste\Checkers\CollectTask\InvalidRequiredDocumentsChecker;
use App\Domain\Waste\Checkers\CollectTask\InvalidStateChecker;
use App\Domain\Waste\Checkers\CollectTask\MissingRequiredDocumentsChecker;
use App\Domain\Waste\DTO\CollectTask\PreExecutionCheckResultDTO;
use App\Domain\Waste\Models\CollectTask;
use App\Domain\Waste\Sorters\PreExecutionCheckSorter;
use Illuminate\Support\Collection;

class PreExecutionCheckAction
{
    public static function execute(Collection $collectTaskIds): Collection
    {
        $tasks = CollectTask::query()
            ->with(['items', 'wasteGenerationPoint.documents.documentType'])
            ->whereIn('id', $collectTaskIds)
            ->get();

        $analyzer = new CollectTaskPreExecutionAnalyzer([
            new InvalidStateChecker(),
            new MissingRequiredDocumentsChecker(),
            new ExpiredRequiredDocumentsChecker(),
            new InvalidRequiredDocumentsChecker(),
            new DuplicateCollectTaskChecker(),
        ]);

        $results = $tasks->map(
            fn (CollectTask $task): PreExecutionCheckResultDTO => $analyzer->analyze($task)
        );

        return (new PreExecutionCheckSorter())->sort($results);
    }
}
