@extends('layouts.app')

@section('title', 'Nueva sesión - StudyAI')

@section('content')

<div class="mx-auto max-w-5xl px-6 py-16">

    <div class="mb-10">

        <a href="{{ route('home') }}"
           class="text-sm text-slate-500 hover:text-indigo-600">
            ← Volver
        </a>

        <h1 class="mt-4 text-4xl font-bold">
            ¿Qué necesitas estudiar?
        </h1>

        <p class="mt-3 text-slate-600">
            Cuéntanos qué materia estás preparando y cuánto tiempo tienes.
        </p>

    </div>


    <div class="grid gap-8 lg:grid-cols-3">

        <form
            action="{{ route('sessions.store') }}"
            method="POST"
            class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm lg:col-span-2">

            @csrf


            <div>

                <label class="mb-2 block font-medium">
                    Materia
                </label>

                <input
                    type="text"
                    name="subject"
                    value="{{ old('subject') }}"
                    placeholder="Ej: Microeconomía"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                >

                @error('subject')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <div>

                <label class="mb-2 block font-medium">
                    Contenido o temario
                </label>

                <textarea
                    name="content"
                    rows="8"
                    placeholder="Ej: Oferta, demanda, equilibrio de mercado, elasticidad..."
                    class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                >{{ old('content') }}</textarea>

                @error('content')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <div>

                <label class="mb-2 block font-medium">
                    Tiempo disponible
                </label>

                <select
                    name="available_time"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500">

                    <option value="">Selecciona una opción</option>
                    <option value="30 minutos">30 minutos</option>
                    <option value="1 hora">1 hora</option>
                    <option value="2 horas">2 horas</option>
                    <option value="Más de 2 horas">Más de 2 horas</option>

                </select>

                @error('available_time')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <button
                type="submit"
                class="w-full rounded-xl bg-indigo-600 px-5 py-4 font-semibold text-white transition hover:bg-indigo-700">

                Crear mi plan de estudio →

            </button>

        </form>


        <div class="h-fit rounded-2xl bg-indigo-50 p-6">

            <h2 class="font-semibold text-indigo-900">
                ¿Qué hará StudyAI?
            </h2>

            <div class="mt-5 space-y-4 text-sm text-indigo-900">

                <p>✓ Identificar los conceptos fundamentales.</p>

                <p>✓ Recomendar por dónde comenzar.</p>

                <p>✓ Ordenar los contenidos.</p>

                <p>✓ Adaptar el estudio al tiempo disponible.</p>

            </div>

        </div>

    </div>

</div>

@endsection