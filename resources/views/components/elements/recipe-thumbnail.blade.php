<script>
    function updateFavStatus(favStatus, recipeId) {
        // Définition de l'id de la recette
        let recipeInput = document.getElementById('recipe-id-input');
        recipeInput.value = recipeId
        // On remet les inputs hidden du formulaire à vide
        let reportInput = document.getElementById("report-input");
        reportInput.value = null;
        let favInput = document.getElementById("fav-input");
        favInput.value = favStatus;
        document.getElementById("status-form").submit();
    }

    function updateReportStatus(reportStatus, recipeId) {
        // Définition de l'id de la recette
        let recipeInput = document.getElementById('recipe-id-input');
        recipeInput.value = recipeId
        // On remet les inputs hidden du formulaire à vide
        let favInput = document.getElementById("fav-input");
        favInput.value = null;
        let reportInput = document.getElementById("report-input");
        reportInput.value = reportStatus;
        document.getElementById("status-form").submit();
    }
</script>

<div {{ $attributes }} class="w-full cursor-pointer group">
    {{-- Si la recette est dans les favoris de l'utilisateur --}}
    @auth
        {{-- Si on est sur la page d'exploration ou du carnet --}}
        @if (Route::currentRouteName() !== 'my-recipes.list')
            <div class="relative">
                {{-- Partie favori --}}
                @if ($attributes->get('isfavorite') == 1)
                    <div title="Retirer des favoris">
                        <span onclick="updateFavStatus(0, {{ $attributes->get('recipeId') }})">
                            <x-fas-heart class="text-veryummy-ternary h-8 cursor-pointer absolute top-1 z-50" />
                        </span>
                    </div>
                @else
                    <div title="Mettre en favori"">
                                    <span onclick=" updateFavStatus(1, {{ $attributes->get('recipeId') }})">
                        <x-far-heart
                            class="text-veryummy-ternary h-8 cursor-pointer absolute invisible group-hover:visible top-1 z-50" />
                        </span>
                    </div>
                @endif
                {{-- Partie signalement --}}
                @if ($attributes->get('isreported') == 1)
                    <div title="Retirer le signalement">
                        <span onclick="updateReportStatus(0, {{ $attributes->get('recipeId') }})">
                            <x-fas-exclamation-triangle
                                class="text-veryummy-ternary h-8 cursor-pointer absolute left-48 top-1 z-50" />
                        </span>
                    </div>
                @else
                    <div title="Signaler la recette"">
                                            <span onclick=" updateReportStatus(1, {{ $attributes->get('recipeId') }})">
                        <x-fas-exclamation-triangle
                            class=" text-veryummy-ternary h-8 cursor-pointer absolute invisible group-hover:visible left-48 top-1 z-50" />
                        </span>
                    </div>
                @endif
            </div>
        @endif

    @endauth
    <a
        href="{{ Route::currentRouteName() === 'my-recipes.list' ? route('my-recipes.edit', [$attributes->get('recipeId')]) : route('recipe.show', [$attributes->get('recipeId')]) }}">
        <div>
            <img class="w-60 h-40 object-cover rounded-sm mb-2 mx-auto"
                src="{{ asset('/img/thumbnail/' . $attributes->get('photo')) }}" alt="test">
        </div>
        <div class="bg-gray-100 drop-shadow-md rounded-sm w-60">
            <p class="my-0 text-center h-36 pt-2"><span
                    class="leading-none text-veryummy-primary text-3xl">{!! strlen($attributes->get('recipeName')) <= 90 ? $attributes->get('recipeName') : substr($attributes->get('recipeName'), 0, 90) . '...' !!}</span>
            </p>
            <p class="my-0"><span
                    class="leading-none pl-4 text-veryummy-secondary text-left text-3xl">{{ $attributes->get('stepCount') }}
                    ETAPES
                    - {{ $attributes->get('ingredientsCount') }}
                    INGREDIENTS</span>
            </p>
            <p class="my-0"><span
                    class="leading-none pl-4 text-veryummy-secondary text-left text-3xl">PREPARATION:
                    {{ $attributes->get('makingTime') }} MINUTES</span>
            </p>
            <p class="my-0">
                @if ($attributes->get('cookingTime') > 0)
                    <span class="leading-none pl-4 text-veryummy-secondary text-left text-3xl">CUISSON:
                        {{ $attributes->get('cookingTime') }} MINUTES</span>
                @endif
            </p>
            @if ($attributes->get('score'))
                <p class="my-0 leading-none">
                <ul class="flex items-center gap-x-1 h-8">
                    <li class="pt-2"><span
                            class="justify-start pl-4 text-veryummy-ternary text-left text-3xl">{{ $attributes->get('score') }}/5</span>
                        <span class="justify-end"></span>
                    </li>
                    {{-- Définition des 5 étoiles de note --}}
                    @for ($e = 1; $e <= 5; $e++)
                        @php
                            $test = $attributes->get('score') - $e;
                        @endphp
                        @switch($test)
                            {{-- Etoile pleine --}}
                            @case($test > 0)
                                <x-fas-star class="text-veryummy-ternary mr-2 h-6 w-6" />
                            @break

                            {{-- Moitié d'étoile --}}
                            @case($test >= -0.5)
                                <x-fas-star-half-alt class="text-veryummy-ternary mr-2 h-6 w-6" />
                            @break

                            {{-- Etoile vide --}}

                            @default
                                <x-far-star class="text-veryummy-ternary mr-2 h-6 w-6" />
                        @endswitch
                    @endfor
                </ul>
                </p>
            @else
                <p class="text-veryummy-ternary text-center text-3xl">PAS DE NOTE</p>
            @endif
        </div>
    </a>
</div>
