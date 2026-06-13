@extends('layouts.app')

@section('content')
    <section class="bg-white dark:bg-gray-900">
        <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
            <div class="grid col-span-full">
                <h1 class="mb-5 text-3xl font-semibold">
                    Просмотр задачи: {{ $task->name }}
                    @auth
                        <a href="{{ route('tasks.edit', $task) }}"
                            class="text-gray-600 hover:text-gray-900 text-2xl ml-2">&#9881;</a>
                    @endauth
                </h1>

                <p><span class="font-bold">Имя:</span> {{ $task->name }}</p>
                <p><span class="font-bold">Статус:</span> {{ $task->status->name }}</p>
                <p><span class="font-bold">Описание:</span> {{ $task->description ?? '' }}</p>

                @if ($task->labels->count() > 0)
                    <p class="mt-2"><span class="font-bold">Метки:</span></p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach ($task->labels as $label)
                            <div
                                class="text-xs inline-flex items-center font-bold leading-sm uppercase px-3 py-1 bg-blue-600 text-white rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ $label->name }}
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </section>
@endsection
