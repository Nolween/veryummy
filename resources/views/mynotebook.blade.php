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
    {{-- JAVASCRIPT --}}
    <script>

    </script>
</head>

@php
$recipes = [
    ['name' => 'Gâteau au chocolat', 'stepCount' => 9, 'cookingTime' => 32, 'makingTime' => 86, 'score' => 1.8, 'photo' => '1.avif', 'ingredientsCount' => 4],
    ['name' => 'Hamburger du Nord', 'stepCount' => 7, 'cookingTime' => 7, 'makingTime' => 58, 'score' => 3.75, 'photo' => '2.avif', 'ingredientsCount' => 3],
    ['name' => 'Raclette', 'stepCount' => 4, 'cookingTime' => 30, 'makingTime' => 4, 'score' => 2.75, 'photo' => '3.avif', 'ingredientsCount' => 7],
    ['name' => 'Tartiflette', 'stepCount' => 4, 'cookingTime' => 50, 'makingTime' => 32, 'score' => 1.34, 'photo' => '4.avif', 'ingredientsCount' => 6],
    ['name' => 'Tiramisu', 'stepCount' => 3, 'cookingTime' => 8, 'makingTime' => 36, 'score' => 3.78, 'photo' => '5.avif', 'ingredientsCount' => 2],
    ['name' => 'Pancakes', 'stepCount' => 5, 'cookingTime' => 95, 'makingTime' => 70, 'score' => 3.08, 'photo' => '6.avif', 'ingredientsCount' => 7],
    ['name' => 'Pizza américaine', 'stepCount' => 2, 'cookingTime' => 87, 'makingTime' => 9, 'score' => 1.81, 'photo' => '7.avif', 'ingredientsCount' => 2],
    ['name' => "Salade d'été", 'stepCount' => 8, 'cookingTime' => 63, 'makingTime' => 52, 'score' => 2.66, 'photo' => '8.avif', 'ingredientsCount' => 8],
    ['name' => 'Gateau aux fraises', 'stepCount' => 9, 'cookingTime' => 61, 'makingTime' => 18, 'score' => 2.53, 'photo' => '9.avif', 'ingredientsCount' => 6],
    ['name' => 'Salade végétarienne', 'stepCount' => 3, 'cookingTime' => 21, 'makingTime' => 92, 'score' => 3.28, 'photo' => '10.avif', 'ingredientsCount' => 7],
    ['name' => 'Oeufs mimosa', 'stepCount' => 2, 'cookingTime' => 61, 'makingTime' => 25, 'score' => 4.54, 'photo' => '11.avif', 'ingredientsCount' => 1],
    ['name' => 'Salade de pâtes', 'stepCount' => 5, 'cookingTime' => 85, 'makingTime' => 86, 'score' => 3.85, 'photo' => '12.avif', 'ingredientsCount' => 3],
    ['name' => 'Kefta', 'stepCount' => 9, 'cookingTime' => 87, 'makingTime' => 91, 'score' => 3.22, 'photo' => '13.avif', 'ingredientsCount' => 1],
    ['name' => 'Salade Vegan', 'stepCount' => 7, 'cookingTime' => 98, 'makingTime' => 54, 'score' => 2.37, 'photo' => '14.avif', 'ingredientsCount' => 6],
    ['name' => 'Burger américain', 'stepCount' => 8, 'cookingTime' => 84, 'makingTime' => 75, 'score' => 1.51, 'photo' => '15.avif', 'ingredientsCount' => 9],
    ['name' => 'Côte de boeuf', 'stepCount' => 9, 'cookingTime' => 51, 'makingTime' => 88, 'score' => 0.71, 'photo' => '16.avif', 'ingredientsCount' => 8],
    ['name' => 'Tacos', 'stepCount' => 8, 'cookingTime' => 74, 'makingTime' => 71, 'score' => 3.96, 'photo' => '17.avif', 'ingredientsCount' => 5],
    ['name' => 'Poisson créole', 'stepCount' => 2, 'cookingTime' => 77, 'makingTime' => 86, 'score' => 3.48, 'photo' => '18.avif', 'ingredientsCount' => 4],
    ['name' => 'Côte de porc', 'stepCount' => 8, 'cookingTime' => 36, 'makingTime' => 83, 'score' => 4.76, 'photo' => '19.avif', 'ingredientsCount' => 8],
    ['name' => 'Salade italienne', 'stepCount' => 1, 'cookingTime' => 98, 'makingTime' => 56, 'score' => 1.81, 'photo' => '20.avif', 'ingredientsCount' => 9],
];
@endphp

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />

        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-9xl w-full text-center">MON CARNET</h1>
        </div>
        <form method="GET" action="/profile">
            @csrf
            @method('GET')
            <div class="flex flex-wrap px-4 lg:px-8 ">
                {{-- Formulaire de recherche --}}
                <div class="w-full lg:w-1/2 lg:pr-2 mb-3">
                    <input type="text" placeholder="RECHERCHER UNE RECETTE" name="name"
                        class="caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                </div>
                {{-- Sélection d'un type de recette --}}
                <div class="w-full lg:w-1/2 lg:pl-2 mb-3">
                    <select name="cars" id="cars"
                        class="text-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                        <option value="1">TOUS</option>
                        <option value="2">VEGETARIEN</option>
                        <option value="3">VEGAN</option>
                        <option value="4">SANS GLUTEN</option>
                    </select>
                </div>

                <div
                    class="w-3/4 flex flex-wrap justify-center sm:justify-between mx-auto text-center my-auto mt-2 mb-12">
                    <span class="text-veryummy-primary text-4xl">2547 RECETTES</span>
                    <a href="{{ route('my-recipes.list') }}">
                        <button type="button" class="bg-white text-3xl" id="newRecipe"><span
                                class="bg-veryummy-secondary pt-3 pb-2 px-3 text-white rounded-sm">MES RECETTES</span></button>
                    </a>
                    <a href="{{ route('my-recipes.new') }}">
                        <button type="button" class="bg-white text-3xl" id="newRecipe"><span
                                class="bg-veryummy-primary pt-3 pb-2 px-3 text-white rounded-sm">NOUVELLE
                                RECETTE</span></button>
                    </a>
                </div>
            </div>
            <div class="w-full mb-3 flex justify-center ">
                <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                    <x-fas-angle-double-left class="h-6 w-6" />
                </button>
                <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                    <x-fas-angle-left class="h-6 w-6" />
                </button>
                <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                    <x-fas-angle-right class="h-6 w-6" />
                </button>
                <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                    <x-fas-angle-double-right class="h-6 w-6" />
                </button>
            </div>
        </form>
        {{-- RECETTES --}}
        <div class="flex flex-wrap mx-8 justify-center">
            @foreach ($recipes as $recipeK => $recipeV)
                <div class="mb-4 mx-3">
                    <x-elements.recipe-thumbnail :photo="$recipeV['photo']" :recipeName="$recipeV['name']" :cookingTime="$recipeV['cookingTime']" :makingTime="$recipeV['makingTime']"
                    :stepCount="$recipeV['stepCount']" :score="$recipeV['score']" :ingredientsCount="$recipeV['ingredientsCount']"/>
                </div>
            @endforeach
        </div>
        <div class="w-full mb-3 flex justify-center ">
            <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                <x-fas-angle-double-left class="h-6 w-6" />
            </button>
            <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                <x-fas-angle-left class="h-6 w-6" />
            </button>
            <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                <x-fas-angle-right class="h-6 w-6" />
            </button>
            <button class="bg-veryummy-secondary rounded-sm text-4xl text-white text-center px-3 mr-3 py-2">
                <x-fas-angle-double-right class="h-6 w-6" />
            </button>
        </div>
    </div>
</body>

</html>
