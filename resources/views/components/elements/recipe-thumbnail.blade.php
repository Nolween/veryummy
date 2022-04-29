<div {{ $attributes }} class="w-full cursor-pointer">

    <a
        href="{{ Route::currentRouteName() === 'my-recipes.list'? route('my-recipes.edit', ['54']): route('my-recipes.view', ['54']) }}">
        <div><img class="w-60 h-40 object-cover rounded-sm mb-2 mx-auto" src="{{ asset('/img/' . $attributes->get('photo')) }}"
            alt="test">
        </div>
        <div class="bg-gray-100 drop-shadow-md rounded-sm">
            <p class="my-0 h-7"><span
                    class="leading-none pl-4 text-veryummy-primary text-left text-3xl">{{ $attributes->get('recipeName') }}</span>
            </p>
            <p class="my-0 h-7"><span
                    class="leading-none pl-4 text-veryummy-secondary text-left text-3xl">{{ $attributes->get('stepCount') }}
                    ETAPES
                    - {{ $attributes->get('ingredientsCount') }}
                    INGREDIENTS</span>
            </p>
            <p class="my-0 h-7"><span
                    class="leading-none pl-4 text-veryummy-secondary text-left text-3xl">PREPARATION:
                    {{ $attributes->get('makingTime') }} MINUTES</span>
            </p>
            <p class="my-0 h-7">
                @if ($attributes->get('cookingTime') > 0)
                    <span class="leading-none pl-4 text-veryummy-secondary text-left text-3xl">CUISSON:
                        {{ $attributes->get('cookingTime') }} MINUTES</span>
                @endif
            </p>
            <p class="my-0 leading-none">
            <ul class="flex items-center gap-x-1 h-8">
                <li class="pt-2"><span
                        class="justify-start pl-4 text-yellow-300 text-left text-3xl">{{ $attributes->get('score') }}/5</span>
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
                            <x-fas-star class="text-yellow-300 mr-2 h-6 w-6" />
                        @break

                        {{-- Moitié d'étoile --}}
                        @case($test >= -0.5)
                            <x-fas-star-half-alt class="text-yellow-300 mr-2 h-6 w-6" />
                        @break

                        {{-- Etoile vide --}}

                        @default
                            <x-far-star class="text-yellow-300 mr-2 h-6 w-6" />
                    @endswitch
                @endfor
            </ul>
            </p>
            <p>
            </p>
        </div>
    </a>
</div>
