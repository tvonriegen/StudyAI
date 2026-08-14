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

    @if(session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @error('progress')
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
            {{ $message }}
        </div>
    @enderror

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                    Tu avance
                </p>

                <h2 class="mt-1 text-xl font-bold text-slate-900">
                    Progreso de la sesión: {{ $progress }}%
                </h2>
            </div>

            <p class="text-sm text-slate-500">
                {{ count($studySession->completedStageIndexes()) }} de {{ count($stages) }} etapas completadas
            </p>
        </div>

        <div
            class="mt-5 flex gap-1.5"
            role="progressbar"
            aria-label="Progreso de la sesión"
            aria-valuenow="{{ $progress }}"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            @foreach($stages as $index => $stage)
                <span
                    class="h-3 flex-1 rounded-full {{ $studySession->stageStatus($index) === 'completed' ? 'bg-indigo-600' : 'bg-slate-200' }}"
                ></span>
            @endforeach
        </div>

    </section>


    <div class="mt-6 space-y-5">

        @foreach($stages as $index => $stage)
            @php($status = $studySession->stageStatus($index))

            <article class="rounded-2xl border bg-white p-6 shadow-sm sm:p-8
                {{ $status === 'in_progress' ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-slate-200' }}">

                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                    <div class="flex gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full font-bold
                            {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500') }}">
                            {{ $status === 'completed' ? '✓' : $index + 1 }}
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-xl font-bold text-slate-900">
                                    {{ $stage['title'] }}
                                </h2>

                                <span class="rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $status === 'completed' ? 'Completado' : ($status === 'in_progress' ? 'En progreso' : 'Pendiente') }}
                                </span>
                            </div>

                            @if(!empty($stage['duration']))
                                <p class="mt-2 text-sm font-medium text-indigo-600">
                                    {{ $stage['duration'] }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($status === 'in_progress')
                        <a
                            href="{{ route('sessions.assistant', ['studySession' => $studySession, 'mode' => 'explanation', 'stage' => $index]) }}"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
                        >
                            Estudiar esta etapa →
                        </a>
                    @elseif($status === 'completed')
                        <a
                            href="{{ route('sessions.assistant', ['studySession' => $studySession, 'mode' => 'explanation', 'stage' => $index]) }}"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-400 hover:text-indigo-600"
                        >
                            Repasar
                        </a>
                    @endif

                </div>

                <div class="mt-6 whitespace-pre-line leading-8 text-slate-700 sm:ml-15">{{ $stage['content'] }}</div>

                @if(!empty($stage['explanation']))
                    <p class="mt-4 leading-8 text-slate-500 sm:ml-15">
                        {{ $stage['explanation'] }}
                    </p>
                @endif

                @if($status === 'in_progress')
                    <form
                        action="{{ route('sessions.stages.complete', ['studySession' => $studySession, 'stage' => $index]) }}"
                        method="POST"
                        class="mt-6 sm:ml-15"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                        >
                            Entendí este tema
                        </button>
                    </form>
                @endif

            </article>
        @endforeach

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
