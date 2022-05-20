<script>
    // Montrer les reports
    function showRepports(place) {
        const reportsList = document.getElementById('reportslist' + place);
        reportsList.classList.add('flex');
        reportsList.classList.remove('hidden');
        const chevronDown = document.getElementById('chevrondown' + place);
        chevronDown.classList.add('inline');
        chevronDown.classList.remove('hidden');
        const chevronRight = document.getElementById('chevronright' + place);
        chevronRight.classList.add('hidden');
        chevronRight.classList.remove('inline');
    }
    // Cacher les repports
    function hideRepports(place) {
        const reportsList = document.getElementById('reportslist' + place);
        reportsList.classList.add('hidden');
        reportsList.classList.remove('flex');
        const chevronDown = document.getElementById('chevrondown' + place);
        chevronDown.classList.add('hidden');
        chevronDown.classList.remove('inline');
        const chevronRight = document.getElementById('chevronright' + place);
        chevronRight.classList.add('inline');
        chevronRight.classList.remove('hidden');
    }

    function allowRecipe(allow, recipeID) {
        let recipeId = document.getElementById("recipe-id-input");
        let allowed = document.getElementById("allow-input");
        recipeId.value = recipeID;
        allowed.value = allow;
        // Soumission du formulaire
        document.getElementById("allow-form").submit();
    }
</script>

<div {{ $attributes }} class="bg-gray-100 drop-shadow-md rounded-sm mb-5 w-full md:w-3/4 lg:w-2/3 mx-3">
    <div class="flex justify-between">
        <div class="pl-3 text-veryummy-ternary text-4xl">{{ $attributes->get('author') }}</div>
        <div class="pr-3 text-veryummy-secondary text-4xl">
            {{ \Carbon\Carbon::parse($attributes->get('date'))->format('d/m/Y h:H') }}</div>
    </div>
    <div class="flex justify-between pr-3">
        <div class="pl-3 text-veryummy-secondary text-4xl">{{ $attributes->get('name') }}</div>
    </div>
    <div class="flex {{ $attributes->get('typelist') == 0 ? 'justify-between' : 'justify-end' }} mb-3 pr-3">
        @if ($attributes->get('typelist') == 0)
            <div class="pl-3 text-veryummy-secondary text-4xl">{{ $attributes->get('reportscount') }} REPORT(S)
                <button type="button">
                    <x-fas-angle-down id="chevrondown{{ $attributes->get('place') }}"
                        onclick="hideRepports({{ $attributes->get('place') }})"
                        class="cursor-pointer hidden pb-2 h-9 w-9 text-veryummy-secondary" />
                </button>
                <button type="button">
                    <x-fas-angle-right id="chevronright{{ $attributes->get('place') }}"
                        onclick="showRepports({{ $attributes->get('place') }})"
                        class="cursor-pointer inline pb-2 h-9 w-9 text-veryummy-secondary" />
                </button>
            </div>
        @endif
        <div class="flex justify-end space-x-2">
            <a href="{{ route('recipe.show', $attributes->get('recipeid')) }}" title="Visiter la page de la recette">
                <x-fas-link class="text-veryummy-secondary h-10 w-10 cursor-pointer" />
            </a>
            <button title="Supprimer les signalements" onclick="allowRecipe(1, {{ $attributes->get('recipeid') }})"
                type="button">
                <x-far-check-square class="text-veryummy-primary h-10 w-10 cursor-pointer" />
            </button>
            <button title="Supprimer la recette" onclick="allowRecipe(0, {{ $attributes->get('recipeid') }})"
                type="button">
                <x-fas-trash-alt class="text-veryummy-ternary h-10 w-10 cursor-pointer" />
            </button>
        </div>
    </div>
    <div class="px-3 hidden flex-wrap space-x-2 mb-3" id="reportslist{{ $attributes->get('place') }}">
        @foreach ($reports as $thK => $thV)
            <button type="button"
                class="px-4 py-3 bg-veryummy-secondary text-white text-2xl rounded-sm">{{ $thV->user->name }}</button>
        @endforeach
    </div>

</div>
