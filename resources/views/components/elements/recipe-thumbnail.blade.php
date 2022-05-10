<div {{ $attributes }} class="w-full cursor-pointer">

    <a
        href="{{ Route::currentRouteName() === 'my-recipes.list'? route('my-recipes.edit', [$attributes->get('recipeId')]): route('recipe.show', [$attributes->get('recipeId')]) }}">
        <div><img class="w-60 h-40 object-cover rounded-sm mb-2 mx-auto" src="{{ asset('/img/' . $attributes->get('photo')) }}"
            alt="test">
        </div>
        <div class="bg-gray-100 drop-shadow-md rounded-sm w-60">
            <p class="my-0 text-center"><span
                    class="leading-none text-veryummy-primary text-3xl">{{ $attributes->get('recipeName') }}</span>
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
            <p>
            </p>
        </div>
    </a>
</div>
