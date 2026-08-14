<?php

use App\Models\StudySession;
use App\Services\GroqService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

function structuredStudyPlan(): string
{
    return json_encode([
        'stages' => [
            [
                'title' => 'Fundamentos',
                'duration' => '15 minutos',
                'content' => 'Estudia los conceptos base.',
                'explanation' => 'Esta etapa prepara las siguientes.',
            ],
            [
                'title' => 'Aplicación',
                'duration' => '20 minutos',
                'content' => 'Aplica los conceptos.',
                'explanation' => 'Permite comprobar la comprensión.',
            ],
            [
                'title' => 'Análisis',
                'duration' => '15 minutos',
                'content' => 'Relaciona los conceptos.',
                'explanation' => 'Profundiza el aprendizaje.',
            ],
            [
                'title' => 'Repaso y práctica',
                'duration' => '10 minutos',
                'content' => 'Repasa y practica.',
                'explanation' => 'Consolida lo aprendido.',
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function createStudySession(array $attributes = []): StudySession
{
    return StudySession::create(array_merge([
        'subject' => 'Microeconomía',
        'content' => 'Oferta, demanda y equilibrio de mercado.',
        'available_time' => '1 hora',
        'study_plan' => structuredStudyPlan(),
    ], $attributes));
}

test('the study plan displays persistent progress states', function () {
    $studySession = createStudySession([
        'completed_stages' => [0],
    ]);

    $this->get(route('sessions.plan', $studySession))
        ->assertSuccessful()
        ->assertSee('Progreso de la sesión: 25%')
        ->assertSee('Completado')
        ->assertSee('En progreso')
        ->assertSee('Pendiente')
        ->assertSee('Aplicación');
});

test('a student completes stages sequentially and progress persists', function () {
    $studySession = createStudySession();

    $this->post(route('sessions.stages.complete', [
        'studySession' => $studySession,
        'stage' => 0,
    ]))->assertRedirect(route('sessions.plan', $studySession));

    $studySession->refresh();

    expect($studySession->completedStageIndexes())->toBe([0])
        ->and($studySession->progressPercentage())->toBe(25)
        ->and($studySession->nextStageIndex())->toBe(1);

    $this->get(route('sessions.plan', $studySession))
        ->assertSuccessful()
        ->assertSee('Progreso de la sesión: 25%');
});

test('a pending stage cannot be completed before the current stage', function () {
    $studySession = createStudySession();

    $this->from(route('sessions.plan', $studySession))
        ->post(route('sessions.stages.complete', [
            'studySession' => $studySession,
            'stage' => 2,
        ]))
        ->assertRedirect(route('sessions.plan', $studySession))
        ->assertSessionHasErrors('progress');

    expect($studySession->fresh()->completedStageIndexes())->toBe([]);
});

test('legacy text plans remain available for progress tracking', function () {
    $studySession = createStudySession([
        'study_plan' => <<<'PLAN'
1. Conceptos base — 15 minutos
Qué estudiar:
Definiciones principales.

2. Aplicación — 20 minutos
Qué estudiar:
Ejercicios simples.

3. Análisis — 15 minutos
Qué estudiar:
Relaciones entre conceptos.

4. Repaso y práctica — 10 minutos
Qué hacer:
Preguntas de práctica.
PLAN,
    ]);

    expect($studySession->studyPlanStages())->toHaveCount(4)
        ->and($studySession->studyPlanStages()[0]['title'])->toBe('Conceptos base');

    $this->get(route('sessions.plan', $studySession))
        ->assertSuccessful()
        ->assertSee('Conceptos base')
        ->assertSee('Progreso de la sesión: 0%');
});

test('creating a session stores the structured plan returned by Groq', function () {
    mock(GroqService::class)
        ->shouldReceive('generateStudyPlan')
        ->once()
        ->andReturn(json_decode(structuredStudyPlan(), true, flags: JSON_THROW_ON_ERROR)['stages']);

    $response = $this->post(route('sessions.store'), [
        'subject' => 'Biología',
        'content' => 'Célula, ADN y síntesis de proteínas.',
        'available_time' => '1 hora',
    ]);

    $studySession = StudySession::sole();

    $response->assertRedirect(route('sessions.plan', $studySession));

    expect($studySession->studyPlanStages())->toHaveCount(4)
        ->and($studySession->progressPercentage())->toBe(0);
});
