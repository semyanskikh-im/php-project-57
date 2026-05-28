@extends('layouts.app')

@section('content')
<section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
        <div class="grid col-span-full">
            <h2 class="mb-5">
                Просмотр задачи: {{ $task->name }}
                @auth
                    <a href="{{ route('tasks.edit', $task) }}">&#9881;</a>
                @endauth
            </h2>
            
            <p><span class="font-black">Имя:</span> {{ $task->name }}</p>
            <p><span class="font-black">Статус:</span> {{ $task->status->name }}</p>
            <p><span class="font-black">Описание:</span> {{ $task->description ?? '' }}</p>
            
            {{-- Метки пока пропускаем --}}
            
            <div class="mt-4">
                <a href="{{ route('tasks.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Назад к списку
                </a>
            </div>
        </div>
    </div>
</section>
@endsection