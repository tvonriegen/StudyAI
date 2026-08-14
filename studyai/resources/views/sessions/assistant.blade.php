@extends('layouts.app')

@section('title', 'Asistente - StudyAI')

@section('content')

<div class="mx-auto max-w-5xl px-6 py-14">


    {{-- ================================================================ --}}
    {{-- VOLVER --}}
    {{-- ================================================================ --}}

    <a
        href="{{ route('sessions.plan', $studySession) }}"
        class="text-sm font-medium text-slate-500 transition hover:text-indigo-600"
    >
        ← Volver a mi plan
    </a>


    {{-- ================================================================ --}}
    {{-- ENCABEZADO --}}
    {{-- ================================================================ --}}

    <div class="mt-6">

        <p class="text-sm font-semibold text-indigo-600">
            {{ $studySession->subject }}
        </p>

        <h1 class="mt-2 text-4xl font-bold tracking-tight text-slate-900">
            Asistente de estudio
        </h1>

        <p class="mt-3 text-slate-600">
            Repasa, comprende y practica los contenidos de tu sesión.
        </p>

    </div>

    @if($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- ================================================================ --}}
    {{-- NAVEGACIÓN --}}
    {{-- ================================================================ --}}

    <div class="mt-8 flex flex-wrap gap-3">

        <a
            href="{{ route('sessions.assistant', $studySession) }}?mode=summary"
            class="rounded-xl px-5 py-3 text-sm font-semibold transition
            {{ $mode === 'summary'
                ? 'bg-indigo-600 text-white'
                : 'border border-slate-300 bg-white text-slate-700 hover:border-indigo-400' }}"
        >
            Resumen
        </a>


        <a
            href="{{ route('sessions.assistant', $studySession) }}?mode=explanation"
            class="rounded-xl px-5 py-3 text-sm font-semibold transition
            {{ $mode === 'explanation'
                ? 'bg-indigo-600 text-white'
                : 'border border-slate-300 bg-white text-slate-700 hover:border-indigo-400' }}"
        >
            Explicación
        </a>


        <a
            href="{{ route('sessions.assistant', $studySession) }}?mode=questions"
            class="rounded-xl px-5 py-3 text-sm font-semibold transition
            {{ $mode === 'questions'
                ? 'bg-indigo-600 text-white'
                : 'border border-slate-300 bg-white text-slate-700 hover:border-indigo-400' }}"
        >
            Preguntas
        </a>

    </div>


    {{-- ================================================================ --}}
    {{-- PREGUNTAS --}}
    {{-- ================================================================ --}}

    @if($mode === 'questions')

        <div class="mt-10">


            {{-- ENCABEZADO --}}

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                        Práctica
                    </p>

                    <h2 class="mt-2 text-3xl font-bold text-slate-900">
                        Preguntas para practicar
                    </h2>

                    <p class="mt-3 text-slate-500">
                        Intenta responder antes de revelar la respuesta.
                    </p>

                </div>


                <a
                    href="{{ route('sessions.assistant', $studySession) }}?mode=questions"
                    class="inline-flex items-center justify-center rounded-xl
                           border border-indigo-200 bg-indigo-50
                           px-5 py-3 text-sm font-semibold text-indigo-700
                           transition hover:bg-indigo-100"
                >
                    ↻ Generar nuevas preguntas
                </a>

            </div>


            {{-- PREGUNTAS --}}

            <div class="mt-8 space-y-5">

                @foreach($questions as $index => $question)

                    <article
                        class="rounded-2xl border border-slate-200
                               bg-white p-7 shadow-sm"
                    >

                        <div class="flex gap-5">

                            <div
                                class="flex h-11 w-11 shrink-0 items-center
                                       justify-center rounded-full
                                       bg-indigo-100 font-semibold
                                       text-indigo-600"
                            >
                                {{ $index + 1 }}
                            </div>


                            <div class="flex-1">

                                <p class="text-lg font-semibold leading-8 text-slate-900">
                                    {{ $question['question'] }}
                                </p>


                                <details
                                    class="mt-5 overflow-hidden rounded-xl
                                           border border-slate-200 bg-slate-50"
                                >

                                    <summary
                                        class="cursor-pointer select-none
                                               px-5 py-4 text-sm
                                               font-semibold text-indigo-600"
                                    >
                                        Mostrar respuesta
                                    </summary>


                                    <div
                                        class="border-t border-slate-200
                                               bg-white px-5 py-5"
                                    >

                                        <p class="text-sm font-bold text-slate-800">
                                            Respuesta
                                        </p>

                                        <p class="mt-2 leading-7 text-slate-600">
                                            {{ $question['answer'] }}
                                        </p>

                                    </div>

                                </details>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        </div>


    {{-- ================================================================ --}}
    {{-- EXPLICACIÓN --}}
    {{-- ================================================================ --}}

    @elseif($mode === 'explanation')


        <div class="mt-10">

            @if($activeStageIndex !== null)
                @php($activeStage = $studySession->studyPlanStages()[$activeStageIndex])

                <div class="mb-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-6">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                        Etapa {{ $activeStageIndex + 1 }} de tu ruta
                    </p>

                    <p class="mt-2 text-lg font-bold text-indigo-950">
                        {{ $activeStage['title'] }}
                    </p>

                    <p class="mt-2 text-sm leading-6 text-indigo-800">
                        La explicación está priorizando el contenido de esta etapa.
                    </p>
                </div>
            @endif


            {{-- ENCABEZADO EXPLICACIÓN --}}

            <div class="mb-10">

                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                    Explicación
                </p>

                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                    {{ $explanation['title'] }}
                </h2>

                <p class="mt-4 max-w-4xl text-lg leading-8 text-slate-600">
                    {{ $explanation['intro'] }}
                </p>

            </div>


            {{-- CONCEPTOS --}}

            <div class="space-y-8">

                @foreach($explanation['sections'] as $index => $section)

                    <article
                        class="rounded-2xl border border-slate-200
                               bg-white p-8 shadow-sm"
                    >


                        {{-- TÍTULO --}}

                        <div class="flex items-center gap-4">

                            <div
                                class="flex h-11 w-11 shrink-0
                                       items-center justify-center
                                       rounded-full bg-indigo-100
                                       font-bold text-indigo-600"
                            >
                                {{ $index + 1 }}
                            </div>


                            <h3 class="text-2xl font-bold text-slate-900">
                                {{ $section['title'] }}
                            </h3>

                        </div>


                        <div class="mt-8 space-y-7 md:ml-15">


                            {{-- ================================================= --}}
                            {{-- QUÉ ES --}}
                            {{-- ================================================= --}}

                            <section>

                                <div class="flex items-center gap-2">

                                    <span class="text-lg">
                                        💡
                                    </span>

                                    <h4 class="font-bold text-slate-900">
                                        ¿Qué es?
                                    </h4>

                                </div>

                                <p class="mt-3 leading-8 text-slate-700">
                                    {{ $section['definition'] }}
                                </p>

                            </section>


                            {{-- ================================================= --}}
                            {{-- CÓMO FUNCIONA --}}
                            {{-- ================================================= --}}

                            <section>

                                <div class="flex items-center gap-2">

                                    <span class="text-lg">
                                        ⚙️
                                    </span>

                                    <h4 class="font-bold text-slate-900">
                                        ¿Cómo funciona?
                                    </h4>

                                </div>

                                <p class="mt-3 leading-8 text-slate-700">
                                    {{ $section['how_it_works'] }}
                                </p>

                            </section>


                            {{-- ================================================= --}}
                            {{-- EJEMPLO --}}
                            {{-- ================================================= --}}

                            @if(!empty($section['example']))

                                <section
                                    class="rounded-xl border border-indigo-100
                                           bg-indigo-50 p-6"
                                >

                                    <p class="font-bold text-indigo-800">
                                        Ejemplo
                                    </p>

                                    <p class="mt-3 leading-8 text-indigo-950">
                                        {{ $section['example'] }}
                                    </p>

                                </section>

                            @endif


                            {{-- ================================================= --}}
                            {{-- CONEXIÓN --}}
                            {{-- ================================================= --}}

                            @if(!empty($section['connection']))

                                <section>

                                    <div class="flex items-center gap-2">

                                        <span class="text-lg">
                                            🔗
                                        </span>

                                        <h4 class="font-bold text-slate-900">
                                            ¿Cómo se relaciona con lo demás?
                                        </h4>

                                    </div>

                                    <p class="mt-3 leading-8 text-slate-700">
                                        {{ $section['connection'] }}
                                    </p>

                                </section>

                            @endif


                            {{-- ================================================= --}}
                            {{-- ADVERTENCIA --}}
                            {{-- ================================================= --}}

                            @if(!empty($section['warning']))

                                <section
                                    class="rounded-xl border border-amber-200
                                           bg-amber-50 p-6"
                                >

                                    <p class="font-bold text-amber-900">
                                        ⚠ Ojo con esto
                                    </p>

                                    <p class="mt-3 leading-8 text-amber-900">
                                        {{ $section['warning'] }}
                                    </p>

                                </section>

                            @endif


                            {{-- ================================================= --}}
                            {{-- PUNTOS CLAVE --}}
                            {{-- ================================================= --}}

                            @if(count($section['key_points']) > 0)

                                <section
                                    class="rounded-xl bg-slate-50 p-6"
                                >

                                    <p class="font-bold text-slate-900">
                                        Qué debes recordar
                                    </p>


                                    <ul class="mt-4 space-y-3">

                                        @foreach($section['key_points'] as $point)

                                            <li
                                                class="flex gap-3 leading-7
                                                       text-slate-700"
                                            >

                                                <span
                                                    class="mt-2 h-2 w-2
                                                           shrink-0 rounded-full
                                                           bg-indigo-500"
                                                ></span>

                                                <span>
                                                    {{ $point }}
                                                </span>

                                            </li>

                                        @endforeach

                                    </ul>

                                </section>

                            @endif

                            <form
                                action="{{ route('sessions.explanation.alternative', $studySession) }}"
                                method="POST"
                            >
                                @csrf
                                <input type="hidden" name="section_index" value="{{ $index }}">

                                @if($activeStageIndex !== null)
                                    <input type="hidden" name="stage" value="{{ $activeStageIndex }}">
                                @endif

                                <button
                                    type="submit"
                                    class="rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                                >
                                    No lo entendí, explícamelo de otra forma
                                </button>
                            </form>

                            @if($alternativeExplanation && $alternativeExplanation['section_index'] === $index)
                                <section class="rounded-xl border border-violet-200 bg-violet-50 p-6">
                                    <p class="font-bold text-violet-900">
                                        Otra forma de entenderlo
                                    </p>

                                    <p class="mt-3 whitespace-pre-line leading-8 text-violet-950">{{ $alternativeExplanation['content'] }}</p>
                                </section>
                            @endif

                        </div>

                    </article>

                @endforeach

            </div>

            @if($activeStageIndex !== null)
                @php($activeStageStatus = $studySession->stageStatus($activeStageIndex))

                <section class="mt-8 rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm sm:p-8">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                        Continúa tu ruta
                    </p>

                    @if($activeStageStatus === 'completed')
                        <h3 class="mt-2 text-xl font-bold text-emerald-700">
                            ✓ Esta etapa ya está completada
                        </h3>
                    @elseif($activeStageStatus === 'in_progress')
                        <h3 class="mt-2 text-xl font-bold text-slate-900">
                            ¿Ya comprendiste esta etapa?
                        </h3>

                        <form
                            action="{{ route('sessions.stages.complete', ['studySession' => $studySession, 'stage' => $activeStageIndex]) }}"
                            method="POST"
                            class="mt-5"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white transition hover:bg-indigo-700"
                            >
                                Entendí este tema
                            </button>
                        </form>
                    @else
                        <p class="mt-2 text-slate-600">
                            Esta etapa estará disponible cuando completes la anterior.
                        </p>
                    @endif
                </section>
            @endif

            <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h3 class="text-xl font-bold text-slate-900">
                    ¿Todavía tienes dudas?
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Pregunta sobre la materia y el contenido original de esta sesión.
                </p>

                <form
                    action="{{ route('sessions.explanation.ask', $studySession) }}"
                    method="POST"
                    class="mt-5 flex flex-col gap-3 sm:flex-row"
                >
                    @csrf

                    @if($activeStageIndex !== null)
                        <input type="hidden" name="stage" value="{{ $activeStageIndex }}">
                    @endif

                    <input
                        type="text"
                        name="question"
                        value="{{ old('question') }}"
                        placeholder="Pregúntale a StudyAI sobre este contenido..."
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                    >

                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Preguntar
                    </button>
                </form>

                @if($freeAnswer)
                    <div class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50 p-6">
                        <p class="text-sm font-semibold text-indigo-700">
                            Tu pregunta
                        </p>

                        <p class="mt-2 font-medium text-indigo-950">
                            {{ $askedQuestion }}
                        </p>

                        <p class="mt-4 whitespace-pre-line leading-8 text-slate-700">{{ $freeAnswer }}</p>
                    </div>
                @endif
            </section>

        </div>


    {{-- ================================================================ --}}
    {{-- RESUMEN --}}
    {{-- ================================================================ --}}

    @else

        <div class="mt-10">


            {{-- CABECERA --}}

            <div class="mb-8">

                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                    Resumen
                </p>

                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                    {{ $studyContent['title'] }}
                </h2>

                <p class="mt-4 max-w-4xl text-lg leading-8 text-slate-600">
                    {{ $studyContent['intro'] }}
                </p>

            </div>


            {{-- SECCIONES RESUMEN --}}

            <div class="space-y-6">

                @foreach($studyContent['sections'] as $index => $section)

                    <article
                        class="rounded-2xl border border-slate-200
                               bg-white p-7 shadow-sm"
                    >

                        <div class="flex items-start gap-5">


                            <div
                                class="flex h-10 w-10 shrink-0
                                       items-center justify-center
                                       rounded-full bg-indigo-100
                                       font-semibold text-indigo-600"
                            >
                                {{ $index + 1 }}
                            </div>


                            <div class="flex-1">

                                <h3 class="text-xl font-bold text-slate-900">
                                    {{ $section['title'] }}
                                </h3>


                                <p class="mt-3 leading-8 text-slate-600">
                                    {{ $section['content'] }}
                                </p>


                                @if(count($section['bullets']) > 0)

                                    <div class="mt-5 rounded-xl bg-slate-50 p-5">

                                        <p class="mb-3 text-sm font-bold text-slate-800">
                                            Puntos clave
                                        </p>


                                        <ul class="space-y-3">

                                            @foreach($section['bullets'] as $bullet)

                                                <li
                                                    class="flex gap-3
                                                           leading-7 text-slate-600"
                                                >

                                                    <span
                                                        class="mt-2 h-2 w-2 shrink-0
                                                               rounded-full bg-indigo-500"
                                                    ></span>

                                                    <span>
                                                        {{ $bullet }}
                                                    </span>

                                                </li>

                                            @endforeach

                                        </ul>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        </div>

    @endif

</div>

@endsection
