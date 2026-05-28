@extends('layouts.app')

@section('content')
<section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
        <div class="grid col-span-full">
            <h1 class="mb-5 text-3xl font-bold">Задачи</h1>

            <div class="w-full flex items-center">
                <div class="ml-auto">
                    @auth
                        <a href="{{ route('tasks.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded ml-2">
                            Создать задачу
                        </a>
                    @endauth
                </div>
            </div>

            <table class="mt-4 w-full">
                <thead class="border-b-2 border-solid border-black text-left">
                    <tr>
                        <th class="px-1 py-1">ID</th>
                        <th class="px-1 py-1">Статус</th>
                        <th class="px-1 py-1">Имя</th>
                        <th class="px-1 py-1">Автор</th>
                        <th class="px-1 py-1">Исполнитель</th>
                        <th class="px-1 py-1">Дата создания</th>
                        @auth
                            <th class="px-1 py-1">Действия</th>
                        @endauth
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr class="border-b border-dashed">
                            <td class="px-1 py-1">{{ $task->id }}</td>
                            <td class="px-1 py-1">{{ $task->status->name }}</td>
                            <td class="px-1 py-1">
                                <a class="text-blue-600 hover:text-blue-900" href="{{ route('tasks.show', $task) }}">
                                    {{ $task->name }}
                                </a>
                            </td>
                            <td class="px-1 py-1">{{ $task->createdBy->name }}</td>
                            <td class="px-1 py-1">{{ $task->assignedTo ? $task->assignedTo->name : '' }}</td>
                            <td class="px-1 py-1">{{ $task->created_at->format('d.m.Y') }}</td>
                            @auth
                                <td class="px-1 py-1 whitespace-nowrap">
                                    @if($task->created_by_id === Auth::id())
                                        <form action="{{ route('tasks.destroy', $task) }}" 
                                              method="POST" 
                                              class="inline-block"
                                              onsubmit="return confirm('Вы уверены?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Удалить
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a class="text-blue-600 hover:text-blue-900 ml-1" 
                                       href="{{ route('tasks.edit', $task) }}">
                                        Изменить
                                    </a>
                                </td>
                            @endauth
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</section>
@endsection