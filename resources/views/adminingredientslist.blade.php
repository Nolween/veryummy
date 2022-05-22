<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <title>Veryummy</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jomhuria:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */
        html {
            line-height: 1.15;
            -webkit-text-size-adjust: 100%
        }

        body {
            margin: 0
        }

    </style>

    <style>
        body {
            font-family: 'Jomhuria', sans-serif;
        }

    </style>
</head>

<body class="antialiased">
    <div id="entire-page">
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-7xl sm:text-9xl w-full text-center">ADMINISTRATION</h1>
        </div>
        <div class="flex flex-wrap justify-around">
            <button type="button"
                class="bg-veryummy-primary text-5xl text-white py-2 px-5 w-56 mb-5">INGREDIENTS</button>
            <a href="{{ route('admin-recipes.list', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">RECETTES</button></a>
            <a href="{{ route('admin-users.list', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">UTILISATEURS</button>
            </a>
        </div>
        {{-- Formulaire --}}
        <form action="{{ route('admin-ingredients.list', $typeList) }}" method="GET">
            @csrf
            @method('GET')
            <div class="flex flex-wrap justify-center mb-7">
                <div class="w-full  lg:w-2/3 mb-5 px-3 text-center">
                    <input placeholder="RECHERCHER" type="text" name="search" value="{{ $search }}"
                        class="pl-3  caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-4/5 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    <button class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                            id="registration-button">
                            RECHERCHER</span></button>
                </div>
                <div class="w-full lg:w-1/3 mb-5 text-center">
                    <a href="{{ route('admin-ingredients.list', 0) }}"><button type="button"
                            class="{{ $typeList == 0 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary'}} text-4xl w-28 p-2 rounded-sm"><span class="text-white"
                                id="registration-button">
                                EN COURS</span></button></a>
                    <a href="{{ route('admin-ingredients.list', 1) }}"><button type="button"
                            class="{{ $typeList == 1 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary'}} text-4xl w-28 p-2 rounded-sm"><span class="text-white"
                                id="registration-button">
                                ACCEPTES</span></button></a>
                    <a href="{{ route('admin-ingredients.list', 2) }}"><button type="button"
                            class="{{ $typeList == 2 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary'}} text-4xl w-28 p-2 rounded-sm"><span class="text-white"
                                id="registration-button">
                                REFUSES</span></button></a>
                </div>

            </div>

        </form>
        <div class="flex flex-wrap justify-center w-full text-center">
            @if (session('ingredientAllowError'))
                <div class=" text-center bg-veryummy-ternary text-white text-3xl w-full mx-2 p-2 mb-2">
                    {{ session('ingredientAllowError') }}</div>
            @endif
            @if (session('ingredientAllowSuccess'))
                <div class=" text-center bg-veryummy-primary text-white text-3xl w-full mx-2 p-2 mb-2">
                    {{ session('ingredientAllowSuccess') }}</div>
            @endif
        </div>
        {{-- Pagination --}}
        <div class="flex justify-center mb-5">
            {{ $ingredients->links() }}
        </div>
        {{-- Eléments --}}
        <form id="allow-form" name="allowForm" action="{{ route('admin-ingredients-allow') }}" method="POST">
            @csrf
            @method('POST')
            <div class="flex flex-wrap justify-center">
                <input id="ingredient-id-input" type="hidden" value="0" name="ingredientid">
                <input id="allow-input" type="hidden" value="0" name="allow">
                <input id="list-type" type="hidden" value="{{ $typeList }}" name="typeList">
                @foreach ($ingredients as $ingredientK => $ingredientV)
                    <x-elements.ingredient-report :ingredientid="$ingredientV->id" :author="$ingredientV->user->name" :date="$ingredientV->updated_at"
                        :name="$ingredientV->name" />
                @endforeach
            </div>
        </form>

        <div class="flex justify-center mb-5">
            {{ $ingredients->links() }}
        </div>
    </div>
</body>

</html>
