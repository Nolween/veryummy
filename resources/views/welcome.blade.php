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
// dd($recipes);
// $recipes = [['name' => 'Hamburger du Nord', 'stepCount' => 3, 'cookingTime' => 20, 'makingTime' => 30, 'score' => 4.2, 'photo' => '2.avif', 'ingredientsCount' => 6], ['name' => 'Raclette', 'stepCount' => 5, 'cookingTime' => 30, 'makingTime' => 50, 'score' => 4.6, 'photo' => '3.avif', 'ingredientsCount' => 7], ['name' => 'Tartiflette', 'stepCount' => 10, 'cookingTime' => 50, 'makingTime' => 20, 'score' => 4.8, 'photo' => '4.avif', 'ingredientsCount' => 8], ['name' => 'Tiramisu', 'stepCount' => 5, 'cookingTime' => 0, 'makingTime' => 20, 'score' => 4.9, 'photo' => '5.avif', 'ingredientsCount' => 9]];
@endphp

<body class="antialiased">
    <div id="entire-page">
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-9xl w-full text-center">VERYUMMY</h1>
        </div>
        {{-- Recettes à la une tirée au hasard --}}
        <div class="flex flex-wrap px-4 justify-center">
            @foreach ($recipes as $recipeK => $recipeV)
                <div class="mb-4 mx-3">
                    <x-elements.recipe-thumbnail :recipeId="$recipeV['id']" :photo="$recipeV['photo']" :recipeName="$recipeV['name']" :cookingTime="$recipeV['cookingTime']" :makingTime="$recipeV['makingTime']"
                        :stepCount="$recipeV['steps_count']" :score="$recipeV['score']" :ingredientsCount="$recipeV['ingredients_count']"/>
                </div>
            @endforeach
        </div>
        {{-- Compteurs --}}
        <div class="my-8">
            <h2 class="h-14 text-veryummy-secondary text-7xl w-full text-center">{{ $counts['totalRecipes'] }} RECETTES</h2>
            <h2 class="h-14 text-veryummy-secondary text-7xl w-full text-center">{{ $counts['totalIngredients'] }} INGREDIENTS
            </h2>
            <h2 class="h-14 text-veryummy-secondary text-7xl w-full text-center">{{ $counts['totalUsers'] }} UTILISATEURS</h2>
        </div>
        <div class="px-4 divide-y-4 divide-dotted divide-gray-200">
            <div class="flex mb-3 flex-wrap justify-center">
                <div class="w-full sm:w-1/2 lg:w-1/3"><a href="{{ route('exploration.list') }}"
                        class="cursor-pointer"><img class="w-full max-h-80 object-cover rounded-sm mb-2 cursor-pointer"
                            src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80"
                            alt="test"></a></div>
                <div class="w-full sm:w-1/2 lg:w-1/3 my-auto">
                    <a href="{{ route('exploration.list') }}" class="cursor-pointer">
                        <p
                            class="h-full px-4 text-veryummy-primary text-5xl md:text-7xl w-full text-center cursor-pointer">
                            EXPLORER DE
                            NOUVELLES RECETTES</p>
                    </a>
                </div>
            </div>
            <div class="pt-4 flex flex-wrap justify-center mb-12">
                <div class="w-full sm:w-1/2  lg:w-1/3 my-auto ">
                    <a href="{{ route('my-recipes.list') }}" class="cursor-pointer">
                        <p
                            class="h-full px-4 text-veryummy-primary text-5xl md:text-7xl w-full text-center cursor-pointer">
                            MES RECETTES</p>
                    </a>
                </div>
                <div class="w-full sm:w-1/2 lg:w-1/3 cursor-pointer"><a href="{{ route('my-recipes.list') }}"
                        class="cursor-pointer"><img class="w-full max-h-80 object-cover rounded-sm mb-2"
                            src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80"
                            alt="test"></a></div>
            </div>
        </div>
    </div>
</body>

</html>
