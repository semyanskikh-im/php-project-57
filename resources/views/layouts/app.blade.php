<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Менеджер задач</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
</head>

<body>

    <div id="app">
        <header class="fixed w-full">
            <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900 shadow-md">
                <div class="flex flex-wrap items-center max-w-screen-xl px-4 mx-auto">


                    <div class="flex-shrink-0 w-40">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">Менеджер
                                задач</span>
                        </a>
                    </div>


                    <div class="flex-1 flex justify-center">
                        <div class="items-center justify-center hidden lg:flex">
                            <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-8 lg:mt-0">
                                <li>
                                    <a href="{{ route('tasks.index') }}"
                                        class="block py-2 pl-3 pr-4 text-gray-700 hover:text-blue-700 lg:p-0">
                                        Задачи
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task_statuses.index') }}"
                                        class="block py-2 pl-3 pr-4 text-gray-700 hover:text-blue-700 lg:p-0">
                                        Статусы
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('labels.index') }}"
                                        class="block py-2 pl-3 pr-4 text-gray-700 hover:text-blue-700 lg:p-0">
                                        Метки
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex-shrink-0 w-40 flex justify-end">
                        @if (Route::has('login'))
                            <div class="flex items-center gap-4">
                                @auth
                                    <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                        Выход
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                        Вход
                                    </a>

                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                                            Регистрация
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>

                </div>
            </nav>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            @include('flash::message')
        </div>

        @yield('content')

    </div>

</body>

</html>
