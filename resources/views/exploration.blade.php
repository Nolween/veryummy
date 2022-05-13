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
// dd($recipes[0]);
@endphp

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />

        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-9xl w-full text-center">EXPLORATION</h1>
        </div>
        <form method="GET" action="{{route('exploration.list')}}">
            @csrf
            @method('GET')
            <div class="flex flex-wrap px-4 lg:px-8">
                {{-- Formulaire de recherche --}}
                <div class="w-full lg:w-2/5 lg:pr-2 mb-3">
                    <input type="text" placeholder="RECHERCHER UNE RECETTE" name="name" value="{{$search}}"
                        class="text-gray-400 caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                </div>
                {{-- Sélection d'un type de recette --}}
                <div class="w-full lg:w-2/5 lg:pl-2 mb-3">
                    <select name="type" id="type-select"
                        class="text-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                        <option {{$type == 0 ? 'selected' : ''}} value="0">TOUS</option>
                        <option {{$type == 1 ? 'selected' : ''}} value="1">VEGETARIEN</option>
                        <option {{$type == 2 ? 'selected' : ''}} value="2">VEGAN</option>
                        <option {{$type == 3 ? 'selected' : ''}} value="3">SANS GLUTEN</option>
                        <option {{$type == 4 ? 'selected' : ''}} value="4">HALAL</option>
                        <option {{$type == 5 ? 'selected' : ''}} value="5">CASHER</option>
                    </select>
                </div>
                <div class="w-full lg:w-1/5 pt-1 pl-3 text-center">
                    <button type="submit" class="text-3xl p-2 rounded-sm my-auto px-4 bg-veryummy-primary">
                        <span class="text-white my-auto">CHERCHER</span>
                    </button>
                </div>

                <div class="w-full text-center my-auto mt-2 mb-6">
                    <span class="text-veryummy-primary text-4xl">{{ $total }}
                        RECETTE{{ $total > 1 ? 'S' : '' }}</span>
                </div>
            </div>
            <div class="flex justify-center mb-5">
                {{ $recipes->links() }}
            </div>
        </form>
        {{-- RECETTES --}}
        <div class="flex flex-wrap mx-8 justify-center">
            @foreach ($recipes as $recipeK => $recipeV)
                <div class="mb-4 mx-3">
                    <x-elements.recipe-thumbnail :recipeId="$recipeV['id']" :photo="$recipeV['image']" :recipeName="$recipeV['name']" :cookingTime="$recipeV['cooking_time']"
                        :makingTime="$recipeV['making_time']" :stepCount="$recipeV['step_count']" :score="$recipeV['score']" :ingredientsCount="$recipeV['ingredients_count']" />
                </div>
            @endforeach
        </div>
        <div class="flex justify-center mb-5">
            {{ $recipes->links() }}
        </div>
    </div>
</body>

</html>
