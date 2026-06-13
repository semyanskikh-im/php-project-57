@extends('layouts.app')

@section('content')
<section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
        <div class="grid col-span-full">
            <h1 class="mb-5 text-3xl font-bold">Метки</h1>

            <div>
                @auth
                    <a href="{{ route('labels.create') }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Создать метку
                    </a>
                @endauth
            </div>

            <table class="mt-4">
                <thead class="border-b-2 border-solid border-black text-left">
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Описание</th>
                        <th>Дата создания</th>
                        @auth
                            <th>Действия</th>
                        @endauth
                    </tr>
                </thead>
                <tbody>
                    @foreach($labels as $label)
                        <tr class="border-b border-dashed text-left">
                            <td>{{ $label->id }}</td>
                            <td>{{ $label->name }}</td>
                            <td>{{ $label->description ?? '' }}</td>
                            <td>{{ $label->created_at->format('d.m.Y') }}</td>
                            @auth
                                <td>
                                    <form action="{{ route('labels.destroy', $label) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Вы уверены?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Удалить
                                        </button>
                                    </form>
                                    <a class="text-blue-600 hover:text-blue-900 ml-2" 
                                       href="{{ route('labels.edit', $label) }}">
                                        Изменить
                                    </a>
                                </td>
                            @endauth
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection