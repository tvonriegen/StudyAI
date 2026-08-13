<?php

use App\Http\Controllers\StudySessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StudySessionController::class, 'home'])
    ->name('home');

Route::get('/estudiar', [StudySessionController::class, 'create'])
    ->name('sessions.create');

Route::post('/estudiar', [StudySessionController::class, 'store'])
    ->name('sessions.store');

Route::get('/plan/{studySession}', [StudySessionController::class, 'plan'])
    ->name('sessions.plan');

Route::get('/asistente/{studySession}', [StudySessionController::class, 'assistant'])
    ->name('sessions.assistant');