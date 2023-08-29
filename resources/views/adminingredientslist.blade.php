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
    function closeDialogProposition() {
        let modal = document.getElementById("proposition-overlay");
        modal.classList.add('hidden');
        let useridInput = document.getElementById("ingredient-id-allow");
        useridInput.value = 0;
    }

    function closeDialogDeny() {
        let modal = document.getElementById("deny-overlay");
        modal.classList.add('hidden');
        let useridInput = document.getElementById("ingredient-id-deny");
        useridInput.value = 0;
    }

    function validateIngredient() {
        let allowed = document.getElementById("allow-input");
        allowed.value = 1;
        let submitButton = document.getElementById('validate-button');
        submitButton.disabled = true;
        submitButton.classList.remove('bg-veryummy-primary');
        submitButton.classList.add('bg-orange-400');
        submitButton.classList.add('text-white');
        submitButton.textContent = "En cours...";
        // Soumission du formulaire
        document.getElementById("allow-form").submit();
    }

    function denyIngredient() {
        let allowed = document.getElementById("deny-input");
        allowed.value = 1;
        let submitButton = document.getElementById('deny-button');
        submitButton.disabled = true;
        submitButton.classList.remove('bg-veryummy-primary');
        submitButton.classList.add('bg-orange-400');
        submitButton.classList.add('text-white');
        submitButton.textContent = "En cours...";
        // Soumission du formulaire
        document.getElementById("deny-form").submit();
    }
</script>

<body class="antialiased">
    <div id="entire-page">
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-7xl sm:text-9xl w-full text-center">ADMINISTRATION</h1>
        </div>
        <div class="flex flex-wrap justify-around">
            <button type="button"
                class="bg-veryummy-primary text-5xl text-white py-2 px-5 w-56 mb-5">INGREDIENTS</button>
            <a href="{{ route('admin-recipes.index', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">RECETTES</button></a>
            <a href="{{ route('admin-users.index', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">UTILISATEURS</button>
            </a>
        </div>
        {{-- Formulaire --}}
        <form action="{{ route('admin-ingredients.index', $typeList) }}" method="GET">
            @csrf
            @method('GET')
            <div class="flex flex-wrap justify-center mb-7">
                <div class="w-full  lg:w-2/3 mb-5 px-3 text-center">
                    <input placeholder="RECHERCHER" type="text" name="search" value="{{ $search }}"
                        class="pl-3  caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-4/5 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    <button class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                            id="registration-button">
                            RECHERCHER</span></button>
                </div>
                <div class="w-full lg:w-1/3 mb-5 text-center">
                    <a href="{{ route('admin-ingredients.index', 0) }}"><button type="button"
                            class="{{ $typeList == 0 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary' }} text-4xl w-28 p-2 rounded-sm"><span
                                class="text-white" id="registration-button">
                                EN COURS</span></button></a>
                    <a href="{{ route('admin-ingredients.index', 1) }}"><button type="button"
                            class="{{ $typeList == 1 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary' }} text-4xl w-28 p-2 rounded-sm"><span
                                class="text-white" id="registration-button">
                                ACCEPTES</span></button></a>
                    <a href="{{ route('admin-ingredients.index', 2) }}"><button type="button"
                            class="{{ $typeList == 2 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary' }} text-4xl w-28 p-2 rounded-sm"><span
                                class="text-white" id="registration-button">
                                REFUSES</span></button></a>
                </div>

            </div>

        </form>

        {{-- NOTIFICATIONS --}}

        @if ($errors->any())
            <div class="flex flex-wrap justify-center">
                @foreach ($errors->all() as $error)
                    <div
                        class="w-full lg:w-1/2 mb-1 p-1 text-center rounded-sm text-white text-5xl bg-veryummy-ternary">
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif
        <div class="flex flex-wrap justify-center w-full text-center">
            @if (session('ingredientAllowSuccess'))
                <div class=" text-center bg-veryummy-primary text-white text-3xl w-full mx-2 p-2 mb-2">
                    {{ session('ingredientAllowSuccess') }}</div>
            @endif
        </div>
        {{-- Pagination --}}
        <div class="flex justify-center mb-5">
            {{ $ingredients->links() }}
        </div>
        {{-- Eléments --}}
        <div class="flex flex-wrap justify-center">
            @foreach ($ingredients as $ingredientK => $ingredientV)
                <x-elements.ingredient-report :typelist="$typeList" :ingredientid="$ingredientV->id" :author="$ingredientV->user->name" :date="$ingredientV->updated_at" :name="$ingredientV->name" />
            @endforeach
        </div>

        <div class="flex justify-center mb-5">
            {{ $ingredients->links() }}
        </div>
    </div>

    {{-- OVERLAY POUR LA CONFIRMATION DE L'INGREDIENT --}}
    <div class="h-screen bg-black bg-opacity-50 fixed inset-0 z-50 hidden justify-center flex items-center"
        id="proposition-overlay">

        <div class=" bg-white rounded-sm block w-3/4 md:w-1/2 inset-0 px-2">
            <div class="flex justify-center items-center"><span id="validation-dialog-title"
                    class="text-4xl text-center sm:text-5xl md:text-6xl text-veryummy-secondary pl-3">VALIDATION
                    INGREDIENT</span>
            </div>

            <div class="text-center text-4xl text-veryummy-ternary mb-7">
                <p>Veuillez définir le nouvel ingredient</p>
            </div>
            {{-- Formulaire --}}
            <form id="allow-form" name="allow-form" method="POST" action="{{ route('admin-ingredients.allow') }}">
                @method('POST')
                @csrf
                <input id="allow-input" type="hidden" value="1" name="allow">
                <input id="list-type" type="hidden" value="{{ $typeList }}" name="typeList">
                <input id="ingredient-id-allow" type="hidden" value="0" name="ingredientid">
                <input id="ingredient-final-name" type="text" placeholder="NOM FINAL"
                    class="pl-3  caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full px-3 rounded-sm focus:border-gray-400 focus:outline-none mb-3"
                    name="finalname">
                <div>
                    <div>
                        <input id="vegetarian" type="checkbox" name="vegetarian" value="1"
                            class="h-5 w-5 mb-2 mx-1 rounded-full accent-veryummy-primary checked:bg-veryummy-primary cursor-pointer">
                        <label for="vegetarian" id="rules-label"><span
                                class="mx-1 text-3xl text-gray-700 pl-2 cursor-pointer">Végétarien</span></label>
                    </div>
                    <div>
                        <input id="vegan" type="checkbox" name="vegan" value="1"
                            class="h-5 w-5 mb-2 mx-1 rounded-full accent-veryummy-primary checked:bg-veryummy-primary cursor-pointer">
                        <label for="vegan" id="rules-label"><span
                                class="mx-1 text-3xl text-gray-700 pl-2 cursor-pointer">Vegan</span></label>
                    </div>
                    <div>
                        <input id="glutenfree" type="checkbox" name="glutenfree" value="1"
                            class="h-5 w-5 mb-2 mx-1 rounded-full accent-veryummy-primary checked:bg-veryummy-primary cursor-pointer">
                        <label for="glutenfree" id="rules-label"><span
                                class="mx-1 text-3xl text-gray-700 pl-2 cursor-pointer">Sans gluten</span></label>
                    </div>
                    <div>
                        <input id="halal" type="checkbox" name="halal" value="1"
                            class="h-5 w-5 mb-2 mx-1 rounded-full accent-veryummy-primary checked:bg-veryummy-primary cursor-pointer">
                        <label for="halal" id="rules-label"><span
                                class="mx-1 text-3xl text-gray-700 pl-2 cursor-pointer">Halal</span></label>
                    </div>
                    <div>
                        <input id="kosher" type="checkbox" name="kosher" value="1"
                            class="h-5 w-5 mb-2 mx-1 rounded-full accent-veryummy-primary checked:bg-veryummy-primary cursor-pointer">
                        <label for="kosher" id="rules-label"><span
                                class="mx-1 text-3xl text-gray-700 pl-2 cursor-pointer">Casher</span></label>
                    </div>
                </div>
                <div class="flex flex-wrap justify-center md:justify-between mb-5 mt-3">
                    <button type="button" onclick="closeDialogProposition()"
                        class="mx-3 text-4xl px-5 py-2 text-white bg-gray-400 mb-3">ANNULER</button>
                    <button type="button" onclick="validateIngredient()" id="validate-button"
                        class="mx-3 text-4xl px-5 py-2 text-white bg-veryummy-primary mb-3">ACCEPTER</button>
                </div>
            </form>
        </div>
    </div>

    {{-- OVERLAY POUR LA SUPPRESSION DE L'INGREDIENT --}}
    <div class="h-screen bg-black bg-opacity-50 fixed inset-0 z-50 hidden justify-center flex items-center"
        id="deny-overlay">

        <div class=" bg-white rounded-sm block w-3/4 md:w-1/2 inset-0 px-2">
            <div class="flex justify-center items-center"><span id="deny-dialog-title"
                    class="text-4xl text-center sm:text-5xl md:text-6xl text-veryummy-secondary pl-3">REFUS
                    INGREDIENT</span>
            </div>

            <div class="text-center text-4xl text-veryummy-ternary mb-7">
                <p>Expliquez pourquoi l'ingrédient est refusé</p>
            </div>
            {{-- Formulaire --}}
            <form id="deny-form" name="deny-form" method="POST" action="{{ route('admin-ingredients.deny') }}">
                @method('POST')
                @csrf
                <input id="deny-input" type="hidden" value="1" name="deny">
                <input id="list-type" type="hidden" value="{{ $typeList }}" name="typeList">
                <input id="ingredient-id-deny" type="hidden" value="0" name="ingredientid">

                <textarea id="ingredient-deny-message" type="text" placeholder="MESSAGE" name="denymessage"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 px-3"></textarea>

                <div class="flex flex-wrap justify-center md:justify-between mb-5">
                    <button type="button" onclick="closeDialogDeny()"
                        class="mx-3 text-4xl px-5 py-2 text-white bg-gray-400 mb-3">ANNULER</button>
                    <button type="button" onclick="denyIngredient()" id="deny-button"
                        class="mx-3 text-4xl px-5 py-2 text-white bg-veryummy-primary mb-3">REFUSER</button>
                </div>
            </form>
        </div>
    </div>


</body>

</html>
