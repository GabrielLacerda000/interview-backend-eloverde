<?php

use App\Domain\Waste\Actions\CollectTask\PreExecutionCheckAction;
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
