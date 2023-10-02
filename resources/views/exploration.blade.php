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
// dd($recipes[0]->opinion->is_reported);
//         dd($attributes->get('isfavorite') == 1);
@endphp

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />

        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-8xl md:text-9xl w-full text-center">EXPLORATION</h1>
        </div>
        <form method="GET" action="{{ route('exploration.index') }}">
            @csrf
            @method('GET')
            <div class="flex flex-wrap px-4 lg:px-8">
                {{-- Formulaire de recherche --}}
                <div class="w-full lg:w-4/12 lg:pr-2 mb-3">
                    <input type="text" placeholder="RECHERCHER UNE RECETTE" name="name"
                        value="{{ $search }}"
                        class="text-gray-400 caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                </div>
                {{-- Sélection d'un type de recette --}}
                <div class="w-full lg:w-3/12 lg:pl-2 mb-3">
                    <select name="type" id="type-select"
                        class="text-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                        @foreach ($types as $type)
                            <option {{ $type == $type ? 'selected' : '' }} value="{{ $type }}">
                                {{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Sélection d'un type de régime --}}
                <div class="w-full lg:w-3/12 lg:pl-2 mb-3">
                    <select name="diet" id="diet-select"
                        class="text-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
                        <option {{ $diet == 0 ? 'selected' : '' }} value="0">TOUS</option>
                        <option {{ $diet == 1 ? 'selected' : '' }} value="1">VEGETARIEN</option>
                        <option {{ $diet == 2 ? 'selected' : '' }} value="2">VEGAN</option>
                        <option {{ $diet == 3 ? 'selected' : '' }} value="3">SANS GLUTEN</option>
                        <option {{ $diet == 4 ? 'selected' : '' }} value="4">HALAL</option>
                        <option {{ $diet == 5 ? 'selected' : '' }} value="5">CASHER</option>
                    </select>
                </div>
                <div class="w-full lg:w-2/12 pt-1 pl-3 text-center">
                    <button type="submit" class="text-3xl p-2 rounded-sm my-auto px-4 bg-veryummy-primary">
                        <span class="text-white my-auto">CHERCHER</span>
                    </button>
                </div>

                <div class="w-full text-center my-auto mt-2 mb-6">
                    <span class="text-veryummy-primary text-4xl">{{ $total }}
                        RECETTE{{ $total > 1 ? 'S' : '' }}</span>
                </div>
            </div>
            {{-- NOTIFICATIONS --}}
            @if ($errors->any())
                <div class="flex flex-wrap justify-center">
                    @foreach ($errors->all() as $error)
                        <div
                            class="w-full lg:w-1/2 mb-1 p-1 text-center rounded-sm text-white text-5xl bg-veryummy-ternary">
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="flex justify-center">
                @if (session('statusSuccess'))
                    <div class="bg-veryummy-primary text-center mb-3 p-2 w-full md:w-1/2">
                        <div class="text-3xl text-white">{{ session('statusSuccess') }}</div>
                    </div>
                @endif
            </div>
            <div class="flex justify-center mb-5">
                {{ $recipes->links() }}
            </div>
        </form>
        {{-- Formulaire pour l'ajout en favori --}}
        <form id="status-form" name="status-form" action="{{ route('recipe.status') }}" method="POST">
            @csrf
            @method('POST')
            <input id="recipe-id-input" type="hidden" value="0" name="recipeid">
            <input id="fav-input" type="hidden" name="is_favorite" value="">
            <input id="report-input" type="hidden" name="is_reported" value="">
            <div class="flex flex-wrap mx-8 justify-center">
                @foreach ($recipes as $recipeK => $recipeV)
                    <div class="mb-4 mx-3">
                        <x-elements.recipe-thumbnail :recipeId="$recipeV->id" :photo="$recipeV->image" :recipeName="$recipeV->name"
                            :cookingTime="$recipeV->cooking_time" :makingTime="$recipeV->making_time" :stepCount="$recipeV->steps_count" :score="$recipeV->score" :ingredientsCount="$recipeV->ingredients_count"
                            :isfavorite="$recipeV->opinion->is_favorite ?? null" :isreported="$recipeV->opinion->is_reported ?? null" />
                    </div>
                @endforeach
            </div>
        </form>
        <div class="flex justify-center mb-5">
            {{ $recipes->links() }}
        </div>
    </div>
</body>

</html>
