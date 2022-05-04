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
$ingredients = [['author' => 'Frances Miller', 'name' => 'aut', 'date' => '1469748738'], ['author' => 'Kelly Jaskolski', 'name' => 'aspernatur', 'date' => '1050479899'], ['author' => 'Bobbie Lowe', 'name' => 'necessitatibus', 'date' => '1526462372'], ['author' => 'Milton Buckridge', 'name' => 'ipsam', 'date' => '1379404396'], ['author' => 'Jeannette Cremin', 'name' => 'quasi', 'date' => '1355965332'], ['author' => 'Franklin Skiles', 'name' => 'in', 'date' => '1713049999']];
@endphp
<script>
</script>

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
            <button type="button" class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">RECETTES</button>
            <button type="button"
                class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">UTILISATEURS</button>
        </div>
        {{-- Formulaire --}}
        <form action="GET">
            <div class="flex flex-wrap justify-center mb-7">
                <div class="w-full md:w-2/4 lg:w-2/3 mb-5 px-3 text-center">
                    <input placeholder="RECHERCHER" type="text" name="search"
                        class="pl-3  caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-4/5 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="w-full md:w-1/4 lg:w-1/3 mb-5 text-center">
                    <button class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                            id="registration-button">
                            RECHERCHER</span></button>
                </div>

            </div>
        </form>
        {{-- Eléments --}}
        <div class="flex flex-wrap justify-center">
            @foreach ($ingredients as $ingredientK => $ingredientV)
                <x-elements.ingredient-report :author="$ingredientV['author']" :date="$ingredientV['date']" :name="$ingredientV['name']" />
            @endforeach
        </div>
    </div>
</body>

</html>
