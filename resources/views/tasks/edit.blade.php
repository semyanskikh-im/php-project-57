@extends('layouts.app')

@section('content')
<section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
        <div class="grid col-span-full">
            <h1 class="mb-5 text-3xl font-bold">Изменение задачи</h1>

            <form class="w-50" method="POST" action="{{ route('tasks.update', $task) }}">
                @csrf
                @method('PUT')
                <div class="flex flex-col">
                    <div>
                        <label for="name">Имя</label>
                    </div>
                    <div class="mt-2">
                        <input class="rounded border-gray-300 w-1/3 @error('name') border-red-500 @enderror" 
                               type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $task->name) }}">
                    </div>
                    @error('name')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-2">
                        <label for="description">Описание</label>
                    </div>
                    <div>
                        <textarea class="rounded border-gray-300 w-1/3 h-32 @error('description') border-red-500 @enderror" 
                                  name="description" 
                                  id="description">{{ old('description', $task->description) }}</textarea>
                    </div>
                    @error('description')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-2">
                        <label for="status_id">Статус</label>
                    </div>
                    <div>
                        <select class="rounded border-gray-300 w-1/3 @error('status_id') border-red-500 @enderror" 
                                name="status_id" 
                                id="status_id">
                            @foreach($taskStatuses as $status)
                                <option value="{{ $status->id }}" {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('status_id')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-2">
                        <label for="assigned_to_id">Исполнитель</label>
                    </div>
                    <div>
                        <select class="rounded border-gray-300 w-1/3 @error('assigned_to_id') border-red-500 @enderror" 
                                name="assigned_to_id" 
                                id="assigned_to_id">
                            <option value=""></option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to_id', $task->assigned_to_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('assigned_to_id')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-2">
                        <label for="labels">Метки</label>
                    </div>
                    <div>
                        <select class="rounded border-gray-300 w-1/3 h-32 @error('labels') border-red-500 @enderror" 
                                name="labels[]" 
                                id="labels" 
                                multiple>
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}" {{ in_array($label->id, old('labels', $task->labels->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $label->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('labels')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-2">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" 
                                type="submit">
                            Обновить
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection