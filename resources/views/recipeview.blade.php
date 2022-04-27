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
            <div class="w-full md:w-1/2 lg:w-1/2">
                <img class="w-full max-h-80 object-cover rounded-2xl mb-2"
                    src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80"
                    alt="test">
            </div>
            <div class="w-full md:w-1/2 lg:w-1/2 px-8 md:px-4 text-6xl md:text-5xl lg:text-5xl text-center md:text-left">
                <ul>
                    <li class="text-veryummy-ternary">5 INGREDIENTS</li>
                    <li class="text-veryummy-ternary">PREPARATION: 20 MINUTES</li>
                    <li class="text-veryummy-ternary">CUISSON: 10 MINUTES</li>
                    <li class="text-veryummy-ternary">5 ETAPES</li>
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
                <ul class="mx-3">
                    @for ($a = 1; $a < 5; $a++)
                        <li class="mb-4 text-gray-400 text-justify text-4xl md:text-5xl">{{ $a }} .
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum nihil inventore expedita
                            accusantium architecto in eligendi fugiat autem quaerat, tenetur possimus cupiditate
                            voluptatem nulla nam reprehenderit ad a saepe beatae.</li>
                    @endfor
                </ul>
            </div>
        </div>

    </div>
</body>

</html>
