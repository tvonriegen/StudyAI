<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Services\GroqService;
use Illuminate\Http\Request;

class StudySessionController extends Controller
{
    public function __construct(
        private GroqService $groq
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Inicio
    |--------------------------------------------------------------------------
    */

    public function home()
    {
        return view('home');
    }


    /*
    |--------------------------------------------------------------------------
    | Crear nueva sesión
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('sessions.create');
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar sesión + generar plan
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'available_time' => ['required', 'string'],
        ]);


        $systemPrompt = <<<'PROMPT'
Eres StudyAI, un tutor académico para estudiantes universitarios.

Tu tarea es crear una ruta de estudio clara y breve.

Debes:
- ordenar los contenidos desde los conceptos fundamentales hacia los más avanzados;
- indicar qué debe estudiar primero;
- explicar brevemente por qué seguir ese orden;
- adaptar el plan al tiempo disponible;
- utilizar los contenidos entregados por el estudiante;
- responder en español;
- ser concreto y fácil de leer.

Genera exactamente 4 etapas de estudio.

IMPORTANTE:
- No uses Markdown.
- No uses asteriscos.
- No uses #, ## o ###.
- No uses tablas Markdown.
- No uses LaTeX.
- No uses bloques de código.
- Si necesitas una fórmula, escríbela como texto normal.

Usa este formato:

1. Nombre de la etapa — tiempo aproximado
Qué estudiar:
Explicación breve:

2. Nombre de la etapa — tiempo aproximado
Qué estudiar:
Explicación breve:

3. Nombre de la etapa — tiempo aproximado
Qué estudiar:
Explicación breve:

4. Repaso y práctica — tiempo aproximado
Qué hacer:
Explicación breve:
PROMPT;


        $userPrompt = "
Materia:
{$validated['subject']}

Tiempo disponible:
{$validated['available_time']}

Contenido o temario:
{$validated['content']}
";


        $validated['study_plan'] = $this->groq->generate(
            $systemPrompt,
            $userPrompt
        );


        $studySession = StudySession::create($validated);


        return redirect()->route(
            'sessions.plan',
            $studySession
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar plan
    |--------------------------------------------------------------------------
    */

    public function plan(StudySession $studySession)
    {
        return view(
            'sessions.plan',
            compact('studySession')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Asistente
    |--------------------------------------------------------------------------
    */

    public function assistant(
        Request $request,
        StudySession $studySession
    ) {

        /*
        |--------------------------------------------------------------------------
        | Modo seleccionado
        |--------------------------------------------------------------------------
        */

        $mode = $request->query('mode', 'summary');


        if (! in_array($mode, [
            'summary',
            'explanation',
            'questions',
        ])) {
            $mode = 'summary';
        }


        /*
        |--------------------------------------------------------------------------
        | Información base que recibe Groq
        |--------------------------------------------------------------------------
        */

        $userPrompt = "
Materia:
{$studySession->subject}

Contenido o temario:
{$studySession->content}
";


        /*
        |--------------------------------------------------------------------------
        | PREGUNTAS
        |--------------------------------------------------------------------------
        */

        if ($mode === 'questions') {

            $sessionKey =
                'studyai_questions_'.$studySession->id;


            $previousQuestions = session(
                $sessionKey,
                []
            );


            if (count($previousQuestions) > 0) {

                $questionsToAvoid = implode(
                    "\n",
                    array_map(
                        fn ($question) => '- '.$question,
                        $previousQuestions
                    )
                );

            } else {

                $questionsToAvoid =
                    'No existen preguntas anteriores.';
            }


            $systemPrompt = <<<'PROMPT'
Eres StudyAI, un tutor académico universitario.

Genera exactamente 5 preguntas NUEVAS de práctica
basadas en los contenidos entregados.

Para cada pregunta genera también su respuesta correcta.

IMPORTANTE:

- No repitas preguntas anteriores.
- No reformules preguntas anteriores cambiando algunas palabras.
- Evalúa distintos aspectos del contenido.
- Evita generar siempre preguntas de definición.
- Las preguntas deben variar en dificultad.

Las cinco preguntas deben tener variedad:

1. Una pregunta conceptual específica.
2. Una pregunta de aplicación.
3. Una pregunta de relación o comparación.
4. Una pregunta de análisis de consecuencias.
5. Una pregunta de integración o razonamiento.

Las respuestas deben:
- explicar brevemente la respuesta;
- ayudar al estudiante a comprender;
- ser claras;
- no ser innecesariamente extensas.

No utilices Markdown.
No utilices asteriscos.
No utilices LaTeX.
Responde en español.
PROMPT;


            $questionPrompt = "
Materia:
{$studySession->subject}

Contenido:
{$studySession->content}

PREGUNTAS QUE YA FUERON UTILIZADAS:

{$questionsToAvoid}

Genera cinco preguntas diferentes
a todas las anteriores.
";


            $questions = $this->groq->generateQuestions(
                $systemPrompt,
                $questionPrompt
            );


            /*
            |--------------------------------------------------------------------------
            | Guardar preguntas anteriores
            |--------------------------------------------------------------------------
            */

            $newQuestions = array_column(
                $questions,
                'question'
            );


            $questionHistory = array_merge(
                $previousQuestions,
                $newQuestions
            );


            $questionHistory = array_slice(
                $questionHistory,
                -25
            );


            session([
                $sessionKey => $questionHistory,
            ]);


            return view('sessions.assistant', [
                'studySession' => $studySession,
                'mode' => 'questions',
                'questions' => $questions,
                'studyContent' => null,
                'explanation' => null,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RESUMEN
        |--------------------------------------------------------------------------
        */

        if ($mode === 'summary') {

            $systemPrompt = <<<'PROMPT'
Eres StudyAI, un tutor académico universitario.

Genera un resumen claro y estructurado utilizando
los contenidos entregados por el estudiante.

El objetivo del resumen es permitir un repaso rápido.

Debes:

- crear un título general;
- escribir una introducción breve;
- identificar los conceptos principales;
- dividirlos en secciones;
- explicar brevemente cada concepto;
- incluir puntos clave;
- priorizar información importante para una evaluación.

El resumen NO debe transformarse en una explicación extensa.

No utilices Markdown.
No utilices asteriscos.
No utilices #.
No utilices LaTeX.
Responde en español.

Si existe una fórmula, escríbela como texto normal.
PROMPT;


            $studyContent = $this->groq->generateStudyContent(
                $systemPrompt,
                $userPrompt
            );


            return view('sessions.assistant', [
                'studySession' => $studySession,
                'mode' => 'summary',
                'studyContent' => $studyContent,
                'questions' => [],
                'explanation' => null,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EXPLICACIÓN
        |--------------------------------------------------------------------------
        */

        if ($mode === 'explanation') {

            $systemPrompt = <<<'PROMPT'
Eres StudyAI, un tutor académico universitario.

Tu función es ENSEÑAR al estudiante.

NO debes entregar simplemente un resumen largo.

Convierte cada concepto importante del contenido
en una pequeña unidad de aprendizaje.

Para CADA concepto genera obligatoriamente:

DEFINICIÓN
Explica qué es el concepto en aproximadamente
2 o 3 oraciones.

CÓMO FUNCIONA
Desarrolla el mecanismo, razonamiento o proceso detrás
del concepto.

Esta es la sección más importante.

Explica:
- qué ocurre;
- por qué ocurre;
- qué provoca;
- cuáles son sus consecuencias;
- cómo debe interpretarlo el estudiante.

Utiliza aproximadamente entre 4 y 7 oraciones
cuando el contenido lo permita.

EJEMPLO
Entrega un ejemplo concreto y fácil de visualizar.

El ejemplo debe permitir aplicar el concepto,
no simplemente volver a repetir la definición.

RELACIÓN CON OTROS CONCEPTOS
Explica cómo ese concepto se conecta con alguno de
los otros temas estudiados.

OJO CON ESTO
Explica una confusión frecuente, error típico,
excepción o aclaración importante.

Debe ayudar al estudiante a evitar aprender mal
el concepto.

QUÉ DEBES RECORDAR
Entrega entre 3 y 5 puntos breves con las ideas que
el estudiante debería recordar para una evaluación.

REGLAS PEDAGÓGICAS:

- Está dirigido a estudiantes universitarios.
- Explica con claridad pero sin simplificar demasiado.
- No te limites a definiciones.
- Explica relaciones causa-efecto.
- Cuando exista una fórmula, explica qué significa.
- Si existen clasificaciones relevantes, explícalas.
- Utiliza ejemplos cuando mejoren la comprensión.
- Puedes complementar el temario con conocimientos
  académicos necesarios para explicar correctamente
  los conceptos.
- No introduzcas temas que no tengan relación con
  lo que el estudiante está estudiando.
- Evita repetir exactamente la misma información
  en distintas secciones.

FORMATO:

- Responde en español.
- No utilices Markdown.
- No utilices asteriscos.
- No utilices #.
- No utilices LaTeX.
- No utilices tablas Markdown.
PROMPT;


            /*
            | IMPORTANTE:
            |
            | Acá usamos generateExplanation(),
            | NO generateStudyContent().
            */

            $explanation = $this->groq->generateExplanation(
                $systemPrompt,
                $userPrompt
            );


            return view('sessions.assistant', [
                'studySession' => $studySession,
                'mode' => 'explanation',
                'explanation' => $explanation,
                'studyContent' => null,
                'questions' => [],
            ]);
        }


        return redirect()->route(
            'sessions.plan',
            $studySession
        );
    }
}