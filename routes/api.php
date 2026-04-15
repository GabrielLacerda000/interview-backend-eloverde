<?php

use App\Domain\Waste\Http\Controllers\CollectTaskController;
use Illuminate\Support\Facades\Route;

Route::post('/collect-tasks/pre-execution-check', [CollectTaskController::class, 'preExecutionCheck']);
