@extends('layouts.app')

@section('title', 'Plan de estudio - StudyAI')

@section('content')

<div class="mx-auto max-w-5xl px-6 py-16">

    <div class="mb-10">

        <a href="{{ route('home') }}"
           class="text-sm text-slate-500 hover:text-indigo-600">
            ← Inicio
        </a>

        <h1 class="mt-4 text-4xl font-bold">
            Tu plan de estudio
        </h1>

        <p class="mt-3 text-slate-600">
            {{ $studySession->subject }}
            ·
            {{ $studySession->available_time }}
        </p>

    </div>


    <div class="mb-6 rounded-2xl border border-indigo-200 bg-indigo-50 p-6">

        <p class="text-sm font-semibold text-indigo-600">
            RUTA RECOMENDADA POR STUDYAI
        </p>

        <p class="mt-2 text-slate-700">
            Organicé tu contenido para que avances desde los
            conceptos fundamentales hacia los más complejos.
        </p>

    </div>


    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

        <div class="whitespace-pre-line leading-8 text-slate-700">{{ $studySession->study_plan }}</div>

    </div>


    <div class="mt-10">

        <h2 class="mb-4 text-lg font-semibold">
            ¿Qué quieres hacer ahora?
        </h2>

        <div class="grid gap-4 md:grid-cols-3">

            <a
                href="{{ route('sessions.assistant', $studySession) }}?mode=summary"
                class="rounded-xl border border-slate-200 bg-white p-5 text-center font-medium shadow-sm transition hover:border-indigo-500 hover:text-indigo-600">

                Resumir contenido

            </a>

            <a
                href="{{ route('sessions.assistant', $studySession) }}?mode=explanation"
                class="rounded-xl border border-slate-200 bg-white p-5 text-center font-medium shadow-sm transition hover:border-indigo-500 hover:text-indigo-600">

                Explicar contenido

            </a>

            <a
                href="{{ route('sessions.assistant', $studySession) }}?mode=questions"
                class="rounded-xl border border-slate-200 bg-white p-5 text-center font-medium shadow-sm transition hover:border-indigo-500 hover:text-indigo-600">

                Generar preguntas

            </a>

        </div>

    </div>

</div>

@endsection