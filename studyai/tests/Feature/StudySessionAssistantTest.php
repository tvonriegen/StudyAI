<?php

use App\Services\GroqService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

function explanationPayload(): array
{
    return [
        'title' => 'Oferta y demanda',
        'intro' => 'Comprende cómo interactúan compradores y vendedores.',
        'sections' => [
            [
                'title' => 'Demanda',
                'definition' => 'La demanda representa lo que los consumidores desean comprar.',
                'how_it_works' => 'Cuando el precio baja, normalmente aumenta la cantidad demandada.',
                'example' => 'Una oferta de cuadernos puede aumentar sus ventas.',
                'connection' => 'Se relaciona con la oferta para formar el equilibrio.',
                'warning' => 'Demanda no es lo mismo que cantidad demandada.',
                'key_points' => ['Depende del precio', 'Refleja decisiones de consumo'],
            ],
        ],
    ];
}

test('summary explanation and questions modes still render Groq responses', function (string $mode, array $payload, string $visibleText) {
    Http::preventStrayRequests();
    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ],
            ]],
        ]),
    ]);

    $studySession = createStudySession();

    $this->get(route('sessions.assistant', [
        'studySession' => $studySession,
        'mode' => $mode,
    ]))
        ->assertSuccessful()
        ->assertSee($visibleText);
})->with([
    'summary' => [
        'summary',
        [
            'title' => 'Resumen de mercado',
            'intro' => 'Ideas principales.',
            'sections' => [[
                'title' => 'Equilibrio',
                'content' => 'Oferta y demanda se encuentran.',
                'bullets' => ['Determina precio y cantidad'],
            ]],
        ],
        'Resumen de mercado',
    ],
    'explanation' => [
        'explanation',
        explanationPayload(),
        'Oferta y demanda',
    ],
    'questions' => [
        'questions',
        [
            'questions' => [[
                'question' => '¿Qué ocurre si aumenta la demanda?',
                'answer' => 'El precio de equilibrio puede aumentar.',
            ]],
        ],
        '¿Qué ocurre si aumenta la demanda?',
    ],
]);

test('explanation can focus on the active study plan stage', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode(explanationPayload(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ],
            ]],
        ]),
    ]);

    $studySession = createStudySession();

    $this->get(route('sessions.assistant', [
        'studySession' => $studySession,
        'mode' => 'explanation',
        'stage' => 0,
    ]))
        ->assertSuccessful()
        ->assertSee('Etapa 1 de tu ruta')
        ->assertSee('Entendí este tema');

    Http::assertSent(fn ($request): bool => str_contains(
        $request->data()['messages'][1]['content'],
        'Fundamentos'
    ));
});

test('a student can request a simpler alternative explanation', function () {
    $studySession = createStudySession();
    $explanation = explanationPayload();

    mock(GroqService::class)
        ->shouldReceive('generate')
        ->once()
        ->andReturn('Imagina la demanda como una fila de personas que quieren comprar entradas.');

    $this->withSession([
        'studyai_explanation_'.$studySession->id => $explanation,
    ])->post(route('sessions.explanation.alternative', $studySession), [
        'section_index' => 0,
        'stage' => 0,
    ])
        ->assertSuccessful()
        ->assertSee('Otra forma de entenderlo')
        ->assertSee('Imagina la demanda como una fila');

    expect($studySession->fresh()->content)->toBe($studySession->content);
});

test('a student can ask a contextual question without storing it', function () {
    $studySession = createStudySession();

    mock(GroqService::class)
        ->shouldReceive('generate')
        ->once()
        ->withArgs(fn (string $systemPrompt, string $userPrompt): bool => str_contains($systemPrompt, 'centrada en la materia')
            && str_contains($userPrompt, $studySession->subject)
            && str_contains($userPrompt, $studySession->content))
        ->andReturn('El equilibrio ocurre cuando la cantidad ofrecida coincide con la demandada.');

    $this->withSession([
        'studyai_explanation_'.$studySession->id => explanationPayload(),
    ])->post(route('sessions.explanation.ask', $studySession), [
        'question' => '¿Cómo se alcanza el equilibrio?',
    ])
        ->assertSuccessful()
        ->assertSee('Tu pregunta')
        ->assertSee('El equilibrio ocurre cuando');

    expect($studySession->fresh()->getAttributes())->not->toHaveKey('question');
});
