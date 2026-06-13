@extends('layouts.app')

@section('content')
<section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
        <div class="grid col-span-full">
            <h1 class="mb-5 text-3xl font-bold">Просмотр метки</h1>

            <div class="flex flex-col">
                <div class="mt-4">
                    <span class="font-black">Имя:</span> {{ $label->name }}
                </div>
                <div class="mt-4">
                    <span class="font-black">Описание:</span> {{ $label->description ?? '' }}
                </div>
                <div class="mt-4">
                    <span class="font-black">Дата создания:</span> {{ $label->created_at->format('d.m.Y') }}
                </div>

                <div class="mt-4">
                    <a href="{{ route('labels.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Назад к списку
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection