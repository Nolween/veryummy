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
            <h1 class="text-veryummy-secondary text-6xl sm:text-8xl md:text-9xl w-full text-center">SCOUBIDOUBIDOU</h1>
        </div>
        {{-- Photo + Résumé --}}
        <div class="flex flex-wrap justify-center px-8 md:px-4 w-3/4 mx-auto">
            <div class="w-full  lg:w-1/2 lg:pr-3">
                <img class="w-full h-full max-h-80 object-cover rounded-2xl mb-2"
                    src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80"
                    alt="test">
            </div>
            <div
                class=" w-full  lg:w-1/2 px-8 md:px-4 text-4xl sm:text-5xl lg:text-5xl text-center md:text-left bg-gray-100 drop-shadow-md rounded-lg">
                <ul class="text-gray-400">
                    <li class="pt-3 flex justify-between">
                        <x-far-heart class="text-veryummy-ternary cursor-pointer" />
                        <x-fas-exclamation-triangle class="text-red-500 cursor-pointer" />
                    </li>
                    <li>5 INGREDIENTS</li>
                    <li>PREPARATION: 20 MINUTES</li>
                    <li>CUISSON: 10 MINUTES</li>
                    <li>5 ETAPES</li>
                    <li class="flex text-yellow-300 justify-between md:justify-end mb-4">
                        <span class="">4.5/5</span>
                        @for ($a = 1; $a < 6; $a++)
                            <x-fas-star class="w-10 h-10 md:ml-2" />
                        @endfor
                    </li>
                </ul>
            </div>
        </div>
        {{-- Ingrédients --}}
        <div class="mx-auto lg:w-3/4 flex flex-wrap justify-center items-center">
            @for ($a = 1; $a < 7; $a++)
                <div class="mx-3 justify-center">
                    <x-icon-abondance class="w-40 h-40 sm:w-60 md:h-60 lg:w-70 lg:h-70 mx-auto" />
                    <div class="text-center text-4xl md:text-5xl text-veryummy-primary">ABONDANCE de BLABLA BLA</div>
                </div>
            @endfor
        </div>
        {{-- Etapes --}}

        <div class="mb-4 pt-20 sm:pt-10">
            <h2 class="text-veryummy-secondary text-4xl sm:text-6xl md:text-7xl w-full text-center">ETAPES</h2>
        </div>
        <div class="w-3/4 justify-center mx-auto">
            <div class="flex flex-wrap ">
                <ul class="mx-3 divide-y-8 divide-dotted divide-veryummy-ternary divide">
                    @for ($a = 1; $a < 5; $a++)
                        <li class="mb-4 pt-4 text-gray-400 text-justify text-4xl md:text-5xl">{{ $a }} .
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum nihil inventore expedita
                            accusantium architecto in eligendi fugiat autem quaerat, tenetur possimus cupiditate
                            voluptatem nulla nam reprehenderit ad a saepe beatae.</li>
                    @endfor
                </ul>
            </div>
        </div>
        {{-- Commentaires --}}

        <div class="mb-4 pt-20 sm:pt-10">
            <h2 class="text-veryummy-secondary text-4xl sm:text-6xl md:text-7xl w-full text-center">COMMENTAIRES</h2>
        </div>
        <div class="w-3/4 justify-center mx-auto">
            @for ($a = 1; $a < 5; $a++)
                <div class="bg-gray-100 drop-shadow-md rounded-lg mb-6 p-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-veryummy-secondary text-5xl">DE JOHN DOE</span>
                        <span class="text-veryummy-secondary text-5xl">20/02/2022 14h30</span>
                    </div>
                    <p class="mb-4 text-gray-400 text-justify text-4xl md:text-5xl">{{ $a }} .
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum nihil inventore expedita
                        accusantium architecto in eligendi fugiat autem quaerat, tenetur possimus cupiditate
                        voluptatem nulla nam reprehenderit ad a saepe beatae.</p>

                    <p class="flex text-yellow-300 justify-end mb-4">
                        <span class="text-5xl">4.5/5</span>
                        @for ($b = 1; $b < 6; $b++)
                            <x-fas-star class="w-6 h-6 md:w-10 md:h-10 ml-3" />
                        @endfor
                        </li>
                    </div>
            @endfor
        </div>
    </div>
</body>

</html>
