<div {{ $attributes }} class="bg-gray-100 drop-shadow-md rounded-sm mb-5 w-full md:w-3/4 lg:w-2/3 mx-3">
    <div class="flex justify-between">
        <div class="pl-3 text-veryummy-ternary text-4xl">{{ $attributes->get('author') }}</div>
        <div class="pr-3 text-veryummy-secondary text-4xl">
            {{ \Carbon\Carbon::createFromTimestamp($attributes->get('date'))->format('d/m/Y h:H') }}</div>
    </div>
    <div class="flex justify-between pr-3">
        <div class="pl-3 text-veryummy-secondary text-4xl">{{ $attributes->get('recipescount') }}
            RECETTE{{ $attributes->get('recipescount') > 1 ? 'S' : '' }}</div>
    </div>
    <div class="flex justify-between mb-3 pr-3">
        <div class="pl-3 text-veryummy-secondary text-4xl">{{ $attributes->get('reportscount') }} REPORT{{ $attributes->get('reportscount') > 1 ? 'S' : '' }}
            <x-fas-angle-down id="chevrondown{{ $attributes->get('place') }}"
                onclick="hideRepports({{ $attributes->get('place') }})"
                class="cursor-pointer hidden pb-2 h-9 w-9 text-veryummy-secondary" />
            <x-fas-angle-right id="chevronright{{ $attributes->get('place') }}"
                onclick="showRepports({{ $attributes->get('place') }})"
                class="cursor-pointer inline pb-2 h-9 w-9 text-veryummy-secondary" />
        </div>
        <div class="flex justify-end space-x-2">
            <x-fas-trash-alt class="text-veryummy-ternary h-10 w-10 cursor-pointer" />
        </div>
    </div>
    <div class="px-3 hidden flex-wrap space-x-2 mb-3" id="reportslist{{ $attributes->get('place') }}">
        @foreach ($reports as $thK => $thV)
            <button type="button"
                class="px-4 py-3 bg-veryummy-secondary text-white text-2xl rounded-sm">{{ $thV }}</button>
        @endforeach
    </div>

</div>

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
</script>
