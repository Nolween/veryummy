<div {{ $attributes }} class="bg-gray-100 drop-shadow-md rounded-sm mb-5 w-full md:w-3/4 lg:w-2/3 mx-3">
    <div class="flex justify-between">
        <div class="pl-3 text-veryummy-ternary text-4xl">{!! $attributes->get('name') !!}</div>
        <div class="pr-3 text-veryummy-secondary text-4xl">
            {{ \Carbon\Carbon::parse($attributes->get('date'))->format('d/m/Y h:H') }}</div>
    </div>
    <div class="flex justify-between mb-3 pr-3">
        <div class="pl-3 text-veryummy-secondary text-4xl">{{ $attributes->get('reportscount') }}
            SIGNALEMENT{{ $attributes->get('reportscount') > 1 ? 'S' : '' }}
            <x-fas-angle-down id="chevrondown{{ $attributes->get('place') }}"
                onclick="hideRepports({{ $attributes->get('place') }})"
                class="cursor-pointer hidden pb-2 h-9 w-9 text-veryummy-secondary" />
            <x-fas-angle-right id="chevronright{{ $attributes->get('place') }}"
                onclick="showRepports({{ $attributes->get('place') }})"
                class="cursor-pointer inline pb-2 h-9 w-9 text-veryummy-secondary" />
        </div>
        <div class="flex justify-end space-x-2" title="Bannir l'utilisateur"
            onclick="openDialogDeletion({{ $attributes->get('userid') }})">
            <x-fas-ban class="text-veryummy-ternary h-10 w-10 cursor-pointer" />
        </div>
    </div>
    <div class="px-3 hidden mb-3" id="reportslist{{ $attributes->get('place') }}">
        @foreach ($opinions as $thK => $thV)
            <div class="px-4 py-3 bg-veryummy-ternary text-white text-3xl rounded-sm mb-3">
                <div>{!! $thV->comment !!}</div>
                <div class="flex justify-end space-x-2">
                    <div class="text-right flex justify-end" title="Supprimer les signalements"
                        onclick="deleteOpinion(0, {{ $thV->id }})">
                        <x-far-check-square class="text-white h-7 w-7 cursor-pointer" />
                    </div>
                    <div class="text-right flex justify-end" title="Supprimer le commentaire"
                        onclick="deleteOpinion(1, {{ $thV->id }})">
                        <x-fas-trash-alt class="text-white h-7 w-7 cursor-pointer" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>




<script>
    function deleteOpinion(destroy, opinionid) {
        // Définition de l'opinion à effacer dans le champ
        let opinionIdInput = document.getElementById("opinion-id-input");
        opinionIdInput.value = opinionid
        // A effacer ou effacer les signalements?
        let destroyInput = document.getElementById("destroy-input");
        destroyInput.value = destroy
        // Soumission du formulaire
        document.getElementById("opinion-deletion-form").submit();
    }

    function openDialogDeletion(userid) {
        let modal = document.getElementById("ban-overlay");
        modal.classList.remove('hidden');
        // Définition de l'utilisateur dans le champ
        let useridInput = document.getElementById("user-id-input");
        useridInput.value = userid
    }

    // Montrer les reports
    function showRepports(place) {
        const reportsList = document.getElementById('reportslist' + place);
        reportsList.classList.add('block');
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
        reportsList.classList.remove('block');
        const chevronDown = document.getElementById('chevrondown' + place);
        chevronDown.classList.add('hidden');
        chevronDown.classList.remove('inline');
        const chevronRight = document.getElementById('chevronright' + place);
        chevronRight.classList.add('inline');
        chevronRight.classList.remove('hidden');
    }
</script>
