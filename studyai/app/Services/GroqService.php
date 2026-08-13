<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqService
{
    public function generate(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::withToken(config('services.groq.key'))
            ->acceptJson()
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model'),

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Error al comunicarse con Groq: '.$response->body()
            );
        }

        $content = $response->json('choices.0.message.content');

        if (! $content) {
            throw new RuntimeException(
                'Groq no devolvió una respuesta válida.'
            );
        }

        return $this->cleanPlainText($content);
    }


    public function generateQuestions(
        string $systemPrompt,
        string $userPrompt
    ): array {
        $response = Http::withToken(config('services.groq.key'))
            ->acceptJson()
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [

                'model' => config('services.groq.model'),

                // Un poco más de variabilidad
                'temperature' => 1.2,

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],

                'response_format' => [
                    'type' => 'json_schema',

                    'json_schema' => [
                        'name' => 'study_questions',
                        'strict' => true,

                        'schema' => [
                            'type' => 'object',

                            'properties' => [
                                'questions' => [
                                    'type' => 'array',

                                    'items' => [
                                        'type' => 'object',

                                        'properties' => [
                                            'question' => [
                                                'type' => 'string',
                                            ],

                                            'answer' => [
                                                'type' => 'string',
                                            ],
                                        ],

                                        'required' => [
                                            'question',
                                            'answer',
                                        ],

                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],

                            'required' => [
                                'questions',
                            ],

                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Error al generar preguntas: '.$response->body()
            );
        }

        $content = $response->json('choices.0.message.content');

        $data = json_decode($content, true);

        if (! isset($data['questions'])) {
            throw new RuntimeException(
                'Groq no devolvió preguntas válidas.'
            );
        }

        return array_slice($data['questions'], 0, 5);
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar formato Markdown
    |--------------------------------------------------------------------------
    */
    public function generateStudyContent(
        string $systemPrompt,
        string $userPrompt
    ): array {
        $response = Http::withToken(config('services.groq.key'))
            ->acceptJson()
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [

                'model' => config('services.groq.model'),

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],

                'response_format' => [
                    'type' => 'json_schema',

                    'json_schema' => [
                        'name' => 'study_content',
                        'strict' => true,

                        'schema' => [
                            'type' => 'object',

                            'properties' => [

                                'title' => [
                                    'type' => 'string',
                                ],

                                'intro' => [
                                    'type' => 'string',
                                ],

                                'sections' => [
                                    'type' => 'array',

                                    'items' => [
                                        'type' => 'object',

                                        'properties' => [

                                            'title' => [
                                                'type' => 'string',
                                            ],

                                            'content' => [
                                                'type' => 'string',
                                            ],

                                            'bullets' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],

                                        'required' => [
                                            'title',
                                            'content',
                                            'bullets',
                                        ],

                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],

                            'required' => [
                                'title',
                                'intro',
                                'sections',
                            ],

                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Error al generar contenido: '.$response->body()
            );
        }

        $content = $response->json('choices.0.message.content');

        $data = json_decode($content, true);

        if (! isset($data['sections'])) {
            throw new RuntimeException(
                'Groq no devolvió contenido válido.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Limpiar cualquier Markdown que Groq haya intentado agregar
        |--------------------------------------------------------------------------
        */

        $data['title'] = $this->cleanPlainText($data['title']);
        $data['intro'] = $this->cleanPlainText($data['intro']);

        foreach ($data['sections'] as &$section) {

            $section['title'] = $this->cleanPlainText(
                $section['title']
            );

            $section['content'] = $this->cleanPlainText(
                $section['content']
            );

            $section['bullets'] = array_map(
                fn ($bullet) => $this->cleanPlainText($bullet),
                $section['bullets']
            );
        }

        return $data;
    }

    public function generateExplanation(
        string $systemPrompt,
        string $userPrompt
    ): array {
        $response = Http::withToken(config('services.groq.key'))
            ->acceptJson()
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [

                'model' => config('services.groq.model'),

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],

                'response_format' => [
                    'type' => 'json_schema',

                    'json_schema' => [
                        'name' => 'study_explanation',
                        'strict' => true,

                        'schema' => [
                            'type' => 'object',

                            'properties' => [

                                'title' => [
                                    'type' => 'string',
                                ],

                                'intro' => [
                                    'type' => 'string',
                                ],

                                'sections' => [
                                    'type' => 'array',

                                    'items' => [
                                        'type' => 'object',

                                        'properties' => [

                                            'title' => [
                                                'type' => 'string',
                                            ],

                                            'definition' => [
                                                'type' => 'string',
                                            ],

                                            'how_it_works' => [
                                                'type' => 'string',
                                            ],

                                            'example' => [
                                                'type' => 'string',
                                            ],

                                            'connection' => [
                                                'type' => 'string',
                                            ],

                                            'warning' => [
                                                'type' => 'string',
                                            ],

                                            'key_points' => [
                                                'type' => 'array',

                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],

                                        'required' => [
                                            'title',
                                            'definition',
                                            'how_it_works',
                                            'example',
                                            'connection',
                                            'warning',
                                            'key_points',
                                        ],

                                        'additionalProperties' => false,
                                    ],
                                ],
                            ],

                            'required' => [
                                'title',
                                'intro',
                                'sections',
                            ],

                            'additionalProperties' => false,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Error al generar explicación: '.$response->body()
            );
        }

        $content = $response->json('choices.0.message.content');

        $data = json_decode($content, true);

        if (! isset($data['sections'])) {
            throw new RuntimeException(
                'Groq no devolvió una explicación válida.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Limpiar texto
        |--------------------------------------------------------------------------
        */

        $data['title'] = $this->cleanPlainText($data['title']);
        $data['intro'] = $this->cleanPlainText($data['intro']);

        foreach ($data['sections'] as &$section) {

            $section['title'] = $this->cleanPlainText(
                $section['title']
            );

            $section['definition'] = $this->cleanPlainText(
                $section['definition']
            );

            $section['how_it_works'] = $this->cleanPlainText(
                $section['how_it_works']
            );

            $section['example'] = $this->cleanPlainText(
                $section['example']
            );

            $section['connection'] = $this->cleanPlainText(
                $section['connection']
            );

            $section['warning'] = $this->cleanPlainText(
                $section['warning']
            );

            $section['key_points'] = array_map(
                fn ($point) => $this->cleanPlainText($point),
                $section['key_points']
            );
        }

        return $data;
    }

    private function cleanPlainText(string $text): string
    {
        // Negritas Markdown
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);

        // Cursivas Markdown
        $text = preg_replace('/\*(.*?)\*/s', '$1', $text);

        // Negrita/cursiva con _
        $text = preg_replace('/__(.*?)__/s', '$1', $text);
        $text = preg_replace('/_(.*?)_/s', '$1', $text);

        // Títulos Markdown
        $text = preg_replace('/^#{1,6}\s*/m', '', $text);

        // Separadores ---
        $text = preg_replace('/^\s*---+\s*$/m', '', $text);

        // Código Markdown
        $text = str_replace(['```', '`'], '', $text);

        // Si Groq dejó algún asterisco suelto
        $text = str_replace('*', '', $text);

        return trim($text);
    }
}