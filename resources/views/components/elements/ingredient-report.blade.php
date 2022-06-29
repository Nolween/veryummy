<script>
    function openDialogProposition(ingredientID, ingredientName) {
        let modal = document.getElementById("proposition-overlay");
        modal.classList.remove('hidden');
        let ingredientTitle = document.getElementById("validation-dialog-title");
        ingredientTitle.innerHTML = ingredientName;
        let ingredientId = document.getElementById("ingredient-id-allow");
        ingredientId.value = ingredientID;
    }

    function openDialogDeny(ingredientID, ingredientName) {
        let modal = document.getElementById("deny-overlay");
        modal.classList.remove('hidden');
        let ingredientTitle = document.getElementById("deny-dialog-title");
        ingredientTitle.innerHTML = ingredientName;
        let ingredientId = document.getElementById("ingredient-id-deny");
        ingredientId.value = ingredientID;
    }
</script>
<div {{ $attributes }} class="bg-gray-100 drop-shadow-md rounded-sm mb-5 w-full md:w-3/4 lg:w-2/3 mx-3">
    <div class="flex {{$attributes->get('typelist') !== 1 ? 'justify-between' : 'justify-end'}}">
        @if ($attributes->get('typelist') !== 1)
        <div class="pl-3 text-veryummy-ternary text-4xl">{!! $attributes->get('author') !!}</div>
        @endif
        <div class="pr-3 text-veryummy-secondary text-4xl">
            {{ \Carbon\Carbon::parse($attributes->get('date'))->format('d/m/Y h:H') }}</div>
    </div>
    <div class="flex justify-between mb-3 pr-3">
        <div class="pl-3 text-veryummy-secondary text-4xl">{!! $attributes->get('name') !!}</div>
        <div class="flex justify-end space-x-2">
            @if ($attributes->get('typelist') !== 1)
                <button
                    onclick="openDialogProposition({{ $attributes->get('ingredientid') }}, {{ Illuminate\Support\Js::from($attributes->get('name')) }})"
                    type="button">
                    <x-fas-check-square class="text-veryummy-primary h-10 w-10" />
                </button>
            @endif
            @if ($attributes->get('typelist') !== 2)
                <button
                    onclick="openDialogDeny({{ $attributes->get('ingredientid') }}, {{ Illuminate\Support\Js::from($attributes->get('name')) }})"
                    type="button">
                    <x-fas-window-close class="text-veryummy-ternary h-10 w-10" />
                </button>
            @endif
        </div>
    </div>
</div>
