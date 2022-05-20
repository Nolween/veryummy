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
@php

// dd( $typeList);
// $recipes = [
//     ['author' => 'Frances Miller', 'reports' => ['Bernadette Collier', 'Julia Shields', 'Kristie Brekke'], 'name' => 'quia pariatur perferendis', 'date' => '1469748738', 'reportsCount' => 3],
//     ['author' => 'Kelly Jaskolski', 'reports' => ['Miss Melissa Bosco', 'Willie Kub', 'Marie Bradtke MD'], 'name' => 'consequatur eos pariatur', 'date' => '1050479899', 'reportsCount' => 3],
//     ['author' => 'Bobbie Lowe', 'reports' => ['Elbert Parisian', 'Bennie Gutkowski', 'Sarah Heidenreich', 'Darren Kris'], 'name' => 'quo occaecati recusandae', 'date' => '1526462372', 'reportsCount' => 4],
//     ['author' => 'Milton Buckridge', 'reports' => ['Jorge Feeney', 'Doyle Conroy'], 'name' => 'nihil quis odio', 'date' => '1379404396', 'reportsCount' => 2],
//     ['author' => 'Jeannette Cremin', 'reports' => ['Guadalupe Greenfelder', 'Francis Walker', 'Genevieve Cormier PhD'], 'name' => 'vel praesentium repellendus', 'date' => '1355965332', 'reportsCount' => 3],
//     ['author' => 'Franklin Skiles', 'reports' => ['Nelson Stamm', 'Bob Goodwin', 'Roland Monahan', 'Miss Darin McGlynn'], 'name' => 'ad ex voluptatem', 'date' => '1713049999', 'reportsCount' => 4],
// ];
@endphp

<body class="antialiased">
    <div id="entire-page">
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-7xl sm:text-9xl w-full text-center">ADMINISTRATION</h1>
        </div>
        <div class="flex flex-wrap justify-around">
            <a href="{{ route('admin-ingredients.list', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">INGREDIENTS</button>
            </a>
            <button type="button" class="bg-veryummy-primary text-5xl text-white py-2 px-5 w-56 mb-5">RECETTES</button>
            <a href="{{ route('admin-userslist') }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">UTILISATEURS</button>
            </a>
        </div>

        {{-- Formulaire --}}
        <form action="GET">
            <div class="flex flex-wrap justify-center mb-7">
                <div class="w-full  lg:w-2/3 mb-5 px-3 text-center">
                    <input placeholder="RECHERCHER" type="text" name="search"
                        class="pl-3  caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-4/5 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    <button class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                            id="registration-button">
                            RECHERCHER</span></button>
                </div>
                <div class="w-full lg:w-1/3 mb-5 text-center">
                    <a href="{{ route('admin-recipes.list', 0) }}"><button type="button"
                            class="bg-veryummy-secondary text-4xl p-2 rounded-sm"><span class="text-white"
                                id="registration-button">
                                EN COURS</span></button></a>
                    <a href="{{ route('admin-recipes.list', 1) }}"><button type="button"
                            class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                                id="registration-button">
                                ACCEPTES</span></button></a>
                </div>

            </div>

        </form>

    </div>
    <div class="flex flex-wrap justify-center w-full text-center">
        @if (session('recipeAllowError'))
            <div class=" text-center bg-veryummy-ternary text-white text-3xl w-full mx-2 p-2 mb-2">
                {{ session('recipeAllowError') }}</div>
        @endif
        @if (session('recipeAllowSuccess'))
            <div class=" text-center bg-veryummy-primary text-white text-3xl w-full mx-2 p-2 mb-2">
                {{ session('recipeAllowSuccess') }}</div>
        @endif
    </div>
    {{-- Pagination --}}
    <div class="flex justify-center mb-5">
        {{ $recipes->links() }}
    </div>
    {{-- Eléments --}}
    <form id="allow-form" name="allow-form" action="{{ route('admin-recipes-allow') }}" method="POST">
        @csrf
        @method('POST')
        <input id="recipe-id-input" type="hidden" value="0" name="recipeid">
        <input id="allow-input" type="hidden" value="0" name="allow">
        <input id="list-type" type="hidden" value="{{ $typeList }}" name="typeList">
        <div class="flex flex-wrap justify-center">
            @foreach ($recipes as $recipeK => $recipeV)
                <x-elements.recipe-report :place="$recipeK" :author="$recipeV->user->name" :date="$recipeV->created_at" :name="$recipeV->name"
                    :reportscount="$recipeV->opinions_count" :reports="$recipeV->opinions" :recipeid="$recipeV->id" :typelist="$typeList" />
            @endforeach
        </div>
    </form>
    {{-- Pagination --}}
    <div class="flex justify-center mb-5">
        {{ $recipes->links() }}
    </div>
    </div>
</body>

</html>
