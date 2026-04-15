<?php

namespace App\Domain\Waste\Http\Controllers;

use App\Domain\Waste\Actions\CollectTask\PreExecutionCheckAction;
use App\Domain\Waste\Http\Requests\PreExecutionCheckRequest;
use App\Domain\Waste\Http\Resources\PreExecutionCheckResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class CollectTaskController extends Controller
{
    public function preExecutionCheck(PreExecutionCheckRequest $request): AnonymousResourceCollection
    {
        $result = PreExecutionCheckAction::execute(
            Collection::make($request->validated('collect_task_ids'))
        );

        return PreExecutionCheckResource::collection($result);
    }
}
