<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'message' => 'Interview Backend Eloverde challenge API',
]));
