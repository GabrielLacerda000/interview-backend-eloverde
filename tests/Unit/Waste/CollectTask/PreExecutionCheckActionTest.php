<?php

use App\Domain\Waste\Actions\CollectTask\PreExecutionCheckAction;
use App\Domain\Waste\Models\CollectTask;
use App\Domain\Waste\Models\CollectTaskItem;
use Database\Seeders\InterviewChallengeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(InterviewChallengeSeeder::class);
});

function runPreExecutionCheck(array $collectTaskIds): Collection
{
    return PreExecutionCheckAction::execute(Collection::make($collectTaskIds));
}

test('it considers a collect task executable when it is in programming and has valid required documents', function (): void {
    $result = runPreExecutionCheck([1])->first();

    expect($result->collect_task_id)->toBe(1);
    expect($result->can_execute)->toBeTrue();
    expect($result->blockers)->toBe([]);
    expect($result->suggested_action)->toBe('execute');
});

test('it blocks collect tasks that are not in programming state', function (): void {
    $result = runPreExecutionCheck([4])->first();

    expect($result->collect_task_id)->toBe(4);
    expect($result->can_execute)->toBeFalse();
    expect($result->blockers)->toContain('invalid_state');
    expect($result->suggested_action)->toBe('fix_state');
});

test('it blocks collect tasks with missing required documents', function (): void {
    $result = runPreExecutionCheck([2])->first();

    expect($result->collect_task_id)->toBe(2);
    expect($result->can_execute)->toBeFalse();
    expect($result->blockers)->toContain('missing_required_documents');
    expect($result->suggested_action)->toBe('review_documents');
});

test('it blocks collect tasks with expired required documents', function (): void {
    $result = runPreExecutionCheck([3])->first();

    expect($result->collect_task_id)->toBe(3);
    expect($result->can_execute)->toBeFalse();
    expect($result->blockers)->toContain('expired_required_documents');
    expect($result->suggested_action)->toBe('review_documents');
});

test('it blocks collect tasks with invalid required documents', function (): void {
    $result = runPreExecutionCheck([9])->first();

    expect($result->collect_task_id)->toBe(9);
    expect($result->can_execute)->toBeFalse();
    expect($result->blockers)->toContain('invalid_required_documents');
    expect($result->suggested_action)->toBe('review_documents');
});

test('it returns multiple blockers when a collect task has more than one problem', function (): void {
    $result = runPreExecutionCheck([5])->first();

    expect($result->collect_task_id)->toBe(5);
    expect($result->can_execute)->toBeFalse();
    expect($result->blockers)->toContain('invalid_state');
    expect($result->blockers)->toContain('missing_required_documents');
    expect($result->suggested_action)->toBe('manual_review');
});

test('it blocks a collect task when a duplicate exists for the same day, point and wastes', function (): void {
    $result = runPreExecutionCheck([7])->first();

    expect($result->collect_task_id)->toBe(7);
    expect($result->can_execute)->toBeFalse();
    expect($result->blockers)->toContain('duplicate_collect_for_same_day');
    expect($result->suggested_action)->toBe('review_or_merge');
    expect($result->related_collect_task_id)->toBe(8);
});

test('it blocks both tasks when they are duplicates of each other', function (): void {
    $results = runPreExecutionCheck([7, 8]);

    $task7 = $results->firstWhere('collect_task_id', 7);
    $task8 = $results->firstWhere('collect_task_id', 8);

    expect($task7->can_execute)->toBeFalse();
    expect($task7->related_collect_task_id)->toBe(8);

    expect($task8->can_execute)->toBeFalse();
    expect($task8->related_collect_task_id)->toBe(7);
});

test('it sets related_collect_task_id to the oldest duplicate when multiple duplicates exist', function (): void {
    $thirdTask = CollectTask::create([
        'waste_generation_point_id' => 7,
        'scheduled_to' => '2026-04-17 15:00:00',
        'state' => CollectTask::STATE_PROGRAMMING,
        'is_urgent' => false,
    ]);

    CollectTaskItem::create(['collect_task_id' => $thirdTask->id, 'waste_id' => 1, 'expected_quantity' => 4]);
    CollectTaskItem::create(['collect_task_id' => $thirdTask->id, 'waste_id' => 2, 'expected_quantity' => 6]);

    $result = runPreExecutionCheck([$thirdTask->id])->first();

    expect($result->can_execute)->toBeFalse();
    expect($result->related_collect_task_id)->toBe(7);
});
