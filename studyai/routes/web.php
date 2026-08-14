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

Route::post('/plan/{studySession}/etapas/{stage}/completar', [StudySessionController::class, 'completeStage'])
    ->whereNumber('stage')
    ->name('sessions.stages.complete');

Route::get('/asistente/{studySession}', [StudySessionController::class, 'assistant'])
    ->name('sessions.assistant');

Route::post('/asistente/{studySession}/otra-explicacion', [StudySessionController::class, 'alternativeExplanation'])
    ->name('sessions.explanation.alternative');

Route::post('/asistente/{studySession}/preguntar', [StudySessionController::class, 'askQuestion'])
    ->name('sessions.explanation.ask');
