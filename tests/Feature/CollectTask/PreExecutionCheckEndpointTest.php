<?php

use Database\Seeders\InterviewChallengeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(InterviewChallengeSeeder::class);
});

test('it returns one analysis for each collect task id', function (): void {
    $response = $this->postJson('/api/collect-tasks/pre-execution-check', [
        'collect_task_ids' => [1, 2],
    ]);

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('it returns an eligible collect task as executable', function (): void {
    $response = $this->postJson('/api/collect-tasks/pre-execution-check', [
        'collect_task_ids' => [1],
    ]);

    expect($response->json('data.0.collect_task_id'))->toBe(1);
    expect($response->json('data.0.can_execute'))->toBeTrue();
    expect($response->json('data.0.blockers'))->toBeArray();
    expect($response->json('data.0.suggested_action'))->toBe('execute');
});

test('it returns a blocked collect task as not executable', function (): void {
    $response = $this->postJson('/api/collect-tasks/pre-execution-check', [
        'collect_task_ids' => [2],
    ]);

    expect($response->json('data.0.collect_task_id'))->toBe(2);
    expect($response->json('data.0.can_execute'))->toBeFalse();
    expect($response->json('data.0.blockers'))->toContain('missing_required_documents');
    expect($response->json('data.0.suggested_action'))->toBe('review_documents');
});

test('it prioritizes urgent blocked collect tasks before urgent eligible ones and normal tasks', function (): void {
    $response = $this->postJson('/api/collect-tasks/pre-execution-check', [
        'collect_task_ids' => [1, 6, 9],
    ]);

    expect($response->json('data.0.collect_task_id'))->toBe(9);
    expect($response->json('data.1.collect_task_id'))->toBe(6);
    expect($response->json('data.2.collect_task_id'))->toBe(1);
});
