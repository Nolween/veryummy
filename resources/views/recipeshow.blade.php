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
<script>
    @php
        $ingredientsSize = count($ingredients);
    @endphp

    function updateServings(mod) {
        // On remet les inputs hidden du formulaire à vide
        let servings = document.getElementById("servings");
        let servingsValue = parseInt(servings.textContent);
        // Si c'est bien un nombre
        if (!isNaN(servingsValue)) {
            servings.textContent = mod === 0 ? (servingsValue - 1) : (servingsValue + 1);
            let ingredientsSize = {{$ingredientsSize}};
            // Modification des quantités des ingrédients
            for (let index = 0; index < ingredientsSize; index++) {
                let quantity = document.getElementById("quantity-" + index);
                quantity.textContent = (parseFloat(quantity.textContent) * servings.textContent / servingsValue).toFixed(2).replace(/(\.0+|0+)$/, '');
            }

        }
    }

    function updateFavStatus(favStatus) {
        // On remet les inputs hidden du formulaire à vide
        let reportInput = document.getElementById("report-input");
        reportInput.value = null;
        let favInput = document.getElementById("fav-input");
        favInput.value = favStatus;
        document.getElementById("status-form").submit();
    }

    function updateReportStatus(reportStatus) {
        // On remet les inputs hidden du formulaire à vide
        let favInput = document.getElementById("fav-input");
        favInput.value = null;
        let reportInput = document.getElementById("report-input");
        reportInput.value = reportStatus;
        document.getElementById("status-form").submit();
    }

    function scoreControl() {
        let scoreInput = document.getElementById("score-input");
        scoreInput.value = scoreInput.value > 5 ? 5 : scoreInput.value < 1 ? 1 : scoreInput.value;
    }
</script>

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-6xl sm:text-8xl md:text-9xl w-full text-center px-2">
                {{ $recipe['name'] }}
            </h1>
        </div>
        {{-- REGIMES --}}

        <div class="flex flex-wrap justify-center px-8 md:px-4 w-3/4 mx-auto mb-6">
            @if ($recipe->vegan_compatible)
                <img src="{{ asset('svg/diet/vegan.svg') }}"
                    class="w-16 h-16 sm:w-20 md:h-20 lg:w-28 lg:h-28 mx-auto" />
            @endif
            @if ($recipe->vegetarian_compatible)
                <img src="{{ asset('svg/diet/végétarien.svg') }}"
                    class="w-16 h-16 sm:w-20 md:h-20 lg:w-28 lg:h-28 mx-auto" />
            @endif
            @if ($recipe->gluten_free_compatible)
                <img src="{{ asset('svg/diet/sans-gluten.svg') }}"
                    class="w-16 h-16 sm:w-20 md:h-20 lg:w-28 lg:h-28 mx-auto" />
            @endif
            @if ($recipe->halal_compatible)
                <img src="{{ asset('svg/diet/halal.svg') }}"
                    class="w-16 h-16 sm:w-20 md:h-20 lg:w-28 lg:h-28 mx-auto" />
            @endif
            @if ($recipe->kosher_compatible)
                <img src="{{ asset('svg/diet/casher.svg') }}"
                    class="w-16 h-16 sm:w-20 md:h-20 lg:w-28 lg:h-28 mx-auto" />
            @endif
        </div>
        {{-- Photo + Résumé --}}
        <div class="flex flex-wrap justify-center px-8 md:px-4 w-3/4 mx-auto mb-6">
            <div class="w-full my-auto lg:w-1/2 lg:pr-3">
                <img class="w-full h-full max-w-80 max-h-80 object-cover rounded-sm mb-2"
                    src="{{ asset('img/full/' . $recipe->image) }}" alt="test">
            </div>
            <div
                class="my-auto w-full lg:w-1/2 px-8 md:px-4 text-4xl sm:text-5xl lg:text-5xl text-center md:text-left bg-slate-50 drop-shadow-md rounded-lg">
                <ul class="text-veryummy-primary">
                    @auth
                        <form id="status-form" action="{{ route('recipe.status', $recipe->id) }}" method="POST">
                            @csrf
                            @method('POST')
                            <input id="fav-input" type="hidden" name="is_favorite" value="">
                            <input id="report-input" type="hidden" name="is_reported" value="">
                            <li class="pt-2 flex justify-between">
                                @if ($opinion && $opinion->is_favorite == true)
                                    <div title="Retirer du carnet">
                                        <x-fas-heart class="text-veryummy-ternary cursor-pointer"
                                            onclick="updateFavStatus(0)" />
                                    </div>
                                @else
                                    <div title="Ajouter à mon carnet">
                                        <x-far-heart class="text-veryummy-ternary cursor-pointer"
                                            onclick="updateFavStatus(1)" />
                                    </div>
                                @endif
                                @if ($opinion && $opinion->is_reported == true)
                                    <div title="Retirer le signalement">
                                        <x-far-check-circle class="text-red-500 cursor-pointer"
                                            onclick="updateReportStatus(0)" />
                                    </div>
                                @else
                                    <div title="Signaler la recette">
                                        <x-fas-exclamation-triangle class="text-red-500 cursor-pointer"
                                            onclick="updateReportStatus(1)" />
                                    </div>
                                @endif
                            </li>
                        </form>
                    @endauth
                    <li class="pt-2">{{ $type }}</li>
                    <li>{{ $recipe['ingredients_count'] }} INGREDIENTS</li>
                    <li>{{ $recipe['steps_count'] }} ETAPES</li>
                    <li>PREPARATION: {{ $recipe['makingTime'] }} MINUTES</li>
                    <li>CUISSON: {{ $recipe['cookingTime'] }} MINUTES</li>
                    <li class="flex text-yellow-400 justify-between md:justify-end mb-4">
                        <span class="">{{ $recipe->score }}/5</span>

                        {{-- Définition des 5 étoiles de note --}}
                        @for ($e = 1; $e <= 5; $e++)
                            @php
                                $test = $recipe->score - $e;
                            @endphp
                            @switch($test)
                                {{-- Etoile pleine --}}
                                @case($test > 0)
                                    <x-fas-star class="text-yellow-400 w-10 h-10 md:ml-2" />
                                @break

                                {{-- Moitié d'étoile --}}
                                @case($test >= -0.5)
                                    <x-fas-star-half-alt class="text-yellow-400 w-10 h-10 md:ml-2" />
                                @break

                                {{-- Etoile vide --}}

                                @default
                                    <x-far-star class="text-yellow-400 w-10 h-10 md:ml-2" />
                            @endswitch
                        @endfor
                    </li>
                </ul>
            </div>
        </div>
        {{-- Nombre de personnes --}}
        <div class="w-full justify-center text-center mb-5">
            <button type="button" class="text-6xl p-2 rounded-sm my-auto px-6 bg-veryummy-primary"
                onclick="updateServings(0)">
                <span class="text-white my-auto"> - </span>
            </button>
            <span class="text-6xl text-veryummy-secondary align-middle ml-3 mr-1"
                id="servings">{{ $recipe['servings'] }}</span>
            <span class="text-6xl text-veryummy-secondary align-middle mr-3">Personnes</span>

            <button type="button" class="text-6xl p-2 rounded-sm my-auto px-6 bg-veryummy-primary"
                onclick="updateServings(1)">
                <span class="text-white my-auto"> + </span>
            </button>
        </div>

        {{-- Ingrédients --}}
        <div class="mx-auto lg:w-3/4 flex flex-wrap justify-center items-center">
            @foreach ($ingredients as $ingredientK => $ingredientV)
                <div class="mx-3 justify-center">
                    <img src="{{ asset('svg/ingredients/' . $ingredientV->ingredient->icon . '.svg') }}"
                        class="w-40 h-40 sm:w-48 md:h-48 lg:w-60 lg:h-60 mx-auto" />
                    <div class="text-center text-3xl md:text-4xl text-veryummy-primary">
                        <span id="quantity-{{ $ingredientK }}">{{ $ingredientV->quantity }}</span>
                        {{ $ingredientV->unit->name }}{{ $ingredientV->quantity > 1 ? 's' : '' }}
                        de {{ $ingredientV->ingredient->name }}
                    </div>
                </div>
            @endforeach
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
                            {{ $stepV->description }}</li>
                    @endforeach

                </ul>
            </div>
        </div>
        {{-- Commentaires --}}

        <div class="mb-4 pt-20 sm:pt-10">
            <h2 class="text-veryummy-secondary text-4xl sm:text-6xl md:text-7xl w-full text-center">COMMENTAIRES</h2>
        </div>
        @auth
            {{-- Si ce n'est pas la recette de l'utilisateur --}}
            @if ($userId !== $recipe->user_id)
                {{-- Ajouter un commentaire --}}
                <form id="comment-form" action="{{ route('recipe.comment', $recipe->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="w-3/4 mx-auto mb-3 flex justify-center">
                        <input id="score-input" type="number" min="1" max="5" step="0.5"
                            placeholder="Note" value="{{ $opinion->score ?? null }}" onchange="scoreControl()"
                            name="score" required
                            class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full  md:w-1/3 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                        @if (!empty($opinion->score))
                            <span class="w-full md:w-1/3 text-center flex justify-center my-auto">
                                {{-- Définition des 5 étoiles de note --}}
                                @for ($e = 1; $e <= 5; $e++)
                                    @php
                                        $testOpinion = $opinion->score - $e;
                                    @endphp
                                    @switch($testOpinion)
                                        {{-- Etoile pleine --}}
                                        @case($testOpinion > 0)
                                            <x-fas-star class="text-veryummy-ternary mr-2 my-auto h-10 w-10" />
                                        @break

                                        {{-- Moitié d'étoile --}}
                                        @case($testOpinion >= -0.5)
                                            <x-fas-star-half-alt class="text-veryummy-ternary mr-2 my-auto h-10 w-10" />
                                        @break

                                        {{-- Etoile vide --}}

                                        @default
                                            <x-far-star class="text-veryummy-ternary mr-2 my-auto h-10 w-10" />
                                    @endswitch
                                @endfor
                            </span>
                        @endif
                    </div>
                    <div class="w-3/4 mx-auto mb-6 text-center flex flex-wrap justify-center">
                        <span class="text-veryummy-primary text-4xl text-center">Votre commentaire</span>
                        <textarea required type="text" placeholder="ECRIVEZ VOTRE COMMENTAIRE" name="comment"
                            class="caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-4 h-40">{{ $opinion->comment ?? null }}</textarea>
                        <div class="text-right my-auto">
                            <button type="submit" class="text-3xl p-2 rounded-sm my-auto px-4 bg-veryummy-primary">
                                <span class="text-white">ENVOYER</span>
                            </button>
                        </div>
                    </div>

                </form>

                @if (!empty($opinion->comment))
                    <div class="w-3/4 mx-auto mb-8 flex justify-center">
                        <form id="delete-form" action="{{ route('recipe-opinion.empty', $recipe->id) }}"
                            method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-3xl p-2 rounded-sm my-auto px-4 bg-veryummy-ternary">
                                <span class="text-white">SUPPRIMER</span> </button>
                        </form>
                    </div>
                @endif
                {{-- FIN Si ce n'est pas la recette de l'utilisateur --}}
            @endif
        @endauth
        {{-- Commentaires existants --}}
        <div class="w-3/4 justify-center mx-auto">
            @foreach ($comments as $commentK => $commentV)
                <div class="bg-slate-50 drop-shadow-md rounded-sm mb-6 p-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-veryummy-primary text-5xl">De {{ $commentV->user->name }}</span>
                        <span
                            class="text-veryummy-primary text-5xl">{{ \Carbon\Carbon::parse($commentV->updated_at)->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <p class="mb-1 text-gray-400 text-justify text-4xl">
                        {{ $commentV->comment }}</p>

                    <p class="flex text-yellow-400 justify-end mb-4">
                        <span class="text-5xl pt-3 pr-2">{{ $commentV->score }}/5</span>

                        {{-- Définition des 5 étoiles de note --}}
                        @for ($e = 1; $e <= 5; $e++)
                            @php
                                $test = $commentV->score - $e;
                            @endphp
                            @switch($test)
                                {{-- Etoile pleine --}}
                                @case($test > 0)
                                    <x-fas-star class="text-yellow-400 mr-2 my-auto h-7 w-7" />
                                @break

                                {{-- Moitié d'étoile --}}
                                @case($test >= -0.5)
                                    <x-fas-star-half-alt class="text-yellow-400 mr-2 my-auto h-7 w-7" />
                                @break

                                {{-- Etoile vide --}}

                                @default
                                    <x-far-star class="text-yellow-400 mr-2 my-auto h-7 w-7" />
                            @endswitch
                        @endfor
                        </li>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>
