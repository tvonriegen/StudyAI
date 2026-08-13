@extends('layouts.app')

@section('title', 'StudyAI')

@section('content')

<section class="mx-auto max-w-6xl px-6 py-24">

    <div class="mx-auto max-w-3xl text-center">

        <span class="mb-5 inline-block rounded-full bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-700">
            Asistente de estudio con IA
        </span>

        <h1 class="text-5xl font-bold tracking-tight text-slate-900">
            Estudia en el orden correcto
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600">
            Ingresa lo que necesitas estudiar y StudyAI te ayudará
            a organizar tu tiempo, priorizar los temas y practicar
            lo aprendido.
        </p>

        <div class="mt-10">

            <a href="{{ route('sessions.create') }}"
               class="rounded-xl bg-indigo-600 px-7 py-4 font-semibold text-white transition hover:bg-indigo-700">

                Comenzar a estudiar →

            </a>

        </div>

    </div>


    <div class="mt-24 grid gap-6 md:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-600">
                1
            </div>

            <h3 class="font-semibold">
                Ingresa tu materia
            </h3>

            <p class="mt-2 text-sm text-slate-600">
                Escribe o pega el contenido que necesitas estudiar.
            </p>

        </div>


        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-600">
                2
            </div>

            <h3 class="font-semibold">
                Obtén tu ruta
            </h3>

            <p class="mt-2 text-sm text-slate-600">
                StudyAI organiza los temas y te indica por dónde comenzar.
            </p>

        </div>


        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-600">
                3
            </div>

            <h3 class="font-semibold">
                Aprende y practica
            </h3>

            <p class="mt-2 text-sm text-slate-600">
                Obtén explicaciones, resúmenes y preguntas de práctica.
            </p>

        </div>

    </div>

</section>

@endsection