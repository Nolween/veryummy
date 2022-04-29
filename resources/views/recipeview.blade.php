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
$recipe = ['name' => 'Hamburger du Nord', 'stepCount' => 7, 'cookingTime' => 7, 'makingTime' => 58, 'score' => 3.75, 'photo' => '2.avif', 'ingredientsCount' => 3];
$ingredients = [['name' => 'abondance', 'svg' => 'abondance', 'quantity' => 10, 'unit' => 2], ['name' => 'anguille', 'svg' => 'anguille', 'quantity' => 300, 'unit' => 2], ['name' => 'arachide', 'svg' => 'arachide', 'quantity' => 5, 'unit' => 3], ['name' => 'cêpes', 'svg' => 'cepes', 'quantity' => 10, 'unit' => 1], ['name' => 'chou cru', 'svg' => 'chou-cru', 'quantity' => 3, 'unit' => 4]];
$steps = ['aliquid assumenda quia', 'Eligendi saepe veritatis cumque. Cum quaerat illum modi nostrum omnis consectetur alias. Dolorum voluptas sequi unde veniam maiores nulla velit. Sapiente omnis id sapiente eaque iusto odio. Qui esse quae fugit explicabo nihil rem dolor.', 'Explicabo ipsum nisi vitae libero alias dolor. Dolorem laboriosam fugiat quam ut.', 'Est et nobis. Amet quisquam cum ullam aspernatur est optio iure fuga. Soluta aut aliquam et et. Quidem consequuntur aliquid voluptatum voluptas ut veritatis iste earum.', 'Eum sit nobis eos cupiditate sint et culpa ipsam. Aut doloribus id facilis cum vel suscipit.'];
$comments = [
    ['author' => 'Edward Cronin Jr.', 'comment' => 'Pariatur et omnis qui magnam ducimus ipsum sit sit. Aperiam sint adipisci. Accusantium occaecati voluptas voluptatem et. Voluptate quibusdam perspiciatis itaque aut sunt.', 'date' => '1651190400', 'score' => '1'],
    ['author' => 'Kellie Rosenbaum', 'comment' => 'Beatae nostrum odit quia a minima et. Nam voluptate laudantium assumenda aut incidunt tenetur commodi voluptatibus. Fugit iure nulla neque.', 'date' => '1518048000', 'score' => '3'],
    ['author' => 'Caleb Fisher', 'comment' => 'Voluptate praesentium nihil perferendis. Quod nostrum illum. Et illo in ut numquam nobis.', 'date' => '1597363200', 'score' => '4'],
    ['author' => 'Estelle Bailey', 'comment' => 'Dignissimos distinctio autem quia eius consequuntur inventore dicta dicta et. Molestiae ratione nisi amet et et. Est sint aperiam recusandae ut sed. Eos nihil doloremque assumenda dolorum et error ipsam consectetur asperiores. Quidem rerum nostrum minus magnam sunt error. Excepturi id reprehenderit facere explicabo ad laudantium vero sit.', 'date' => '1642723200', 'score' => '3'],
];
$yourComment = 'Rerum similique dolor repellendus. In consequatur aut voluptas molestias. Dicta ducimus et sed omnis tempora dolores consequuntur voluptatem dolore. Facere sint blanditiis recusandae dignissimos placeat nihil eum omnis fugiat. Eaque ipsam reiciendis architecto. Est quam animi in ullam sed dolor.';
@endphp

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-6xl sm:text-8xl md:text-9xl w-full text-center">
                {{ $recipe['name'] }}
            </h1>
        </div>
        {{-- Photo + Résumé --}}
        <div class="flex flex-wrap justify-center px-8 md:px-4 w-3/4 mx-auto">
            <div class="w-full  lg:w-1/2 lg:pr-3">
                <img class="w-full h-full max-h-80 object-cover rounded-sm mb-2"
                    src="{{ asset('img/' . $recipe['photo']) }}" alt="test">
            </div>
            <div
                class=" w-full  lg:w-1/2 px-8 md:px-4 text-4xl sm:text-5xl lg:text-5xl text-center md:text-left bg-gray-100 drop-shadow-md rounded-lg">
                <ul class="text-gray-400">
                    <li class="pt-3 flex justify-between">
                        <x-far-heart class="text-veryummy-ternary cursor-pointer" />
                        <x-fas-exclamation-triangle class="text-red-500 cursor-pointer" />
                    </li>
                    <li>{{ $recipe['ingredientsCount'] }} INGREDIENTS</li>
                    <li>PREPARATION: {{ $recipe['makingTime'] }} MINUTES</li>
                    <li>CUISSON: {{ $recipe['cookingTime'] }} MINUTES</li>
                    <li>{{ $recipe['stepCount'] }} ETAPES</li>
                    <li class="flex text-yellow-300 justify-between md:justify-end mb-4">
                        <span class="">{{ $recipe['score'] }}/5</span>

                        {{-- Définition des 5 étoiles de note --}}
                        @for ($e = 1; $e <= 5; $e++)
                            @php
                                $test = $recipe['score'] - $e;
                            @endphp
                            @switch($test)
                                {{-- Etoile pleine --}}
                                @case($test > 0)
                                    <x-fas-star class="text-yellow-300 w-10 h-10 md:ml-2" />
                                @break

                                {{-- Moitié d'étoile --}}
                                @case($test >= -0.5)
                                    <x-fas-star-half-alt class="text-yellow-300 w-10 h-10 md:ml-2" />
                                @break

                                {{-- Etoile vide --}}

                                @default
                                    <x-far-star class="text-yellow-300 w-10 h-10 md:ml-2" />
                            @endswitch
                        @endfor
                    </li>
                </ul>
            </div>
        </div>
        {{-- Ingrédients --}}
        <div class="mx-auto lg:w-3/4 flex flex-wrap justify-center items-center">
            @foreach ($ingredients as $ingredientK => $ingredientV)
                @php
                    $unit = null;
                    switch ($ingredientV['unit']) {
                        case 1:
                            $unit = 'UNITE(S)';
                            break;
                        case 2:
                            $unit = 'GRAMME(S)';
                            break;
                        case 3:
                            $unit = 'CUILLERE(S) A SOUPE';
                            break;
                        case 4:
                            $unit = 'CUILLERE(S) A CAFE';
                            break;
                        case 5:
                            $unit = 'CENTILITRE(S)';
                            break;
                        case 5:
                            $unit = 'LITRE(S)';
                            break;
                    
                        default:
                            $unit = 'UNITE(S)';
                            break;
                    }
                @endphp
                <div class="mx-3 justify-center">
                    <img src="{{ asset('svg/' . $ingredientV['svg'] . '.svg') }}"
                        class="w-40 h-40 sm:w-60 md:h-60 lg:w-70 lg:h-70 mx-auto" />
                    <div class="text-center text-4xl md:text-5xl text-veryummy-primary">
                        {{ $ingredientV['quantity'] }}
                        {{ $unit }}
                        de {{ $ingredientV['name'] }}</div>
                </div>
            @endforeach
            @for ($a = 1; $a < 7; $a++)
            @endfor
        </div>
        {{-- Etapes --}}

        <div class="mb-4 pt-20 sm:pt-10">
            <h2 class="text-veryummy-secondary text-4xl sm:text-6xl md:text-7xl w-full text-center">ETAPES</h2>
        </div>
        <div class="w-3/4 justify-center mx-auto">
            <div class="flex flex-wrap ">
                <ul class="mx-3 divide-y-8 divide-dotted divide-veryummy-ternary divide">
                    @foreach ($steps as $stepK => $stepV)
                        <li class="mb-4 pt-4 text-gray-400 text-justify text-4xl md:text-5xl">{{ $stepK + 1 }} .
                            {{ $stepV }}</li>
                    @endforeach

                </ul>
            </div>
        </div>
        {{-- Commentaires --}}

        <div class="mb-4 pt-20 sm:pt-10">
            <h2 class="text-veryummy-secondary text-4xl sm:text-6xl md:text-7xl w-full text-center">COMMENTAIRES</h2>
        </div>

        {{-- Ajouter un commentaire --}}
        <form method="POST" action="/comment">
            @csrf
            @method('POST')
            <div class="w-3/4 mx-auto mb-8">
                <span class="text-veryummy-primary text-4xl text-center">Votre commentaire</span>
                <textarea type="text" placeholder="ECRIVEZ VOTRE COMMENTAIRE" name="comment"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-1 h-40">{{ $yourComment }}</textarea>
                <div class="text-right my-auto">
                    @if ($yourComment !== null)
                        <button type="button"
                            class="text-3xl p-2 rounded-sm my-auto bg-veryummy-ternary">
                            <span class="text-white">SUPPRIMER</span> </button>
                    @endif
                    <button type="button" class="text-3xl p-2 rounded-sm my-auto bg-veryummy-primary">
                        <span class="text-white">ENVOYER</span>
                    </button>
                </div>
            </div>

        </form>
        {{-- Commentaires existants --}}
        <div class="w-3/4 justify-center mx-auto">
            @foreach ($comments as $commentK => $commentV)
                <div class="bg-gray-100 drop-shadow-md rounded-sm mb-6 p-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-veryummy-secondary text-5xl">De {{ $commentV['author'] }}</span>
                        <span
                            class="text-veryummy-secondary text-5xl">{{ \Carbon\Carbon::createFromTimestamp($commentV['date'])->format('d/m/Y h:H') }}</span>
                    </div>
                    <p class="mb-1 text-gray-400 text-justify text-4xl">
                        {{ $commentV['comment'] }}</p>

                    <p class="flex text-yellow-300 justify-end mb-4">
                        <span class="text-5xl pt-3 pr-2">{{ $commentV['score'] }}/5</span>

                        {{-- Définition des 5 étoiles de note --}}
                        @for ($e = 1; $e <= 5; $e++)
                            @php
                                $test = $commentV['score'] - $e;
                            @endphp
                            @switch($test)
                                {{-- Etoile pleine --}}
                                @case($test > 0)
                                    <x-fas-star class="text-yellow-300 mr-2 my-auto h-7 w-7" />
                                @break

                                {{-- Moitié d'étoile --}}
                                @case($test >= -0.5)
                                    <x-fas-star-half-alt class="text-yellow-300 mr-2 my-auto h-7 w-7" />
                                @break

                                {{-- Etoile vide --}}

                                @default
                                    <x-far-star class="text-yellow-300 mr-2 my-auto h-7 w-7" />
                            @endswitch
                        @endfor
                        </li>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>
