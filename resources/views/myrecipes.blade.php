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
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />

        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-9xl w-full text-center">MES RECETTES</h1>
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
                        <option value="volvo">TOUS</option>
                        <option value="saab">VEGETARIEN</option>
                        <option value="mercedes">VEGAN</option>
                        <option value="audi">SANS GLUTEN</option>
                    </select>
                </div>

                <div class="w-full text-center my-auto mt-2 mb-12">
                    <span class="text-veryummy-ternary text-4xl">2547 RECETTES</span>
                </div>
            </div>
            <div class="w-full mb-3 flex justify-center">
              <button class="bg-veryummy-primary text-4xl text-white text-center px-3 mr-3"><<</button>
              <button class="bg-veryummy-primary text-4xl text-white text-center px-3 mr-3"><</button>
              <button class="bg-veryummy-primary text-4xl text-white text-center px-3 mr-3">></button>
              <button class="bg-veryummy-primary text-4xl text-white text-center px-3 mr-3">>></button>
            </div>
        </form>
        {{-- RECETTES --}}
        <div class="flex flex-wrap mx-8 justify-center">
            @for ($x = 1; $x < 21; $x++)
                <div class="mb-4 mx-3">
                    <x-elements.recipe-thumbnail />
                </div>
            @endfor
        </div>
    </div>
</body>

</html>
