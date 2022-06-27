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

        body {
            font-family: 'Jomhuria', sans-serif;
        }
    </style>
    <style>
        /*the container must be positioned relative:*/
        .autocomplete {
            position: relative;
            display: inline-block;
        }

        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            /*position the autocomplete items to be the same width as the container:*/
            top: 100%;
            left: 0;
            right: 0;
            font-size: 40px;
            color: #62666b;
            font-weight: 100;
        }

        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }

        /*when hovering an item:*/
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }

        /*when navigating through the items using the arrow keys:*/
        .autocomplete-active {
            background-color: DodgerBlue !important;
            color: #ffffff;
        }
    </style>
    <script>
        // Ajout d'un nouvel ingrédient
        function insertIngredient() {
            var count = document.getElementById("ingredientCount")
            count.value = parseInt(count.value) + 1

            // Récupération des unités sous forme d'option de select
            @php
                $unitsOptions = '';
                foreach ($units as $unit) {
                    $unitsOptions .= '<option value="' . $unit->id . '" >' . $unit->name . '</option>';
                }
            @endphp

            let unitsOptions = '{!! $unitsOptions !!}';


            var newIngredient = `<div class="w-full xl:w-3/4 flex flex-wrap my-4 pt-8 px-4 mx-auto justify-center" id="ingredientInputs${count.value}"><div class="autocomplete">
                    <input type="hidden" name="ingredients[${count.value}][ingredientId]" value="0" id="ingredientId${count.value}">
                        <input id="ingredient${count.value}" type="text" name="ingredients[${count.value}][ingredientName]" placeholder="INGREDIENT"
                            class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    </div>
                    <div class="text-center">
                        <input type="number" name="ingredients[${count.value}][ingredientQuantity]" min="0" step="1"
                            class="caret-gray-400 border-gray-100 border-2 text-4xl text-gray-400 w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                        <select name="ingredients[${count.value}][ingredientUnit]"
                            class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                            ${unitsOptions}
                </select>
                        <button onclick="deleteIngredient(${count.value})" type="button" class="bg-veryummy-ternary text-5xl p-2 rounded-sm  align-middle ml-2">
                            <x-fas-trash-alt class="text-white h-6 w-6"/>
                        </button>
                    </div>
                </div>`

            // On ajoute juste avant la fin du parent le nouvel ingrédient
            document.getElementById("ingredients").insertAdjacentHTML('beforeEnd', newIngredient);
            // Ajout des fonctions d'autocomplete
            autocomplete(document.getElementById("ingredient" + count.value), ingredients, count.value);
        }
        // Ajout d'une nouvelle étape
        function insertStep() {
            var count = document.getElementById("stepCount")
            count.value = parseInt(count.value) + 1

            var newStep = `
                <div class="w-full xl:w-3/4 flex flex-wrap my-4 pt-8 px-4 mx-auto justify-center" id="stepInputs${count.value}">
                    <textarea type="text" placeholder="ETAPE" name="steps[${count.value}][stepDescription]"
                        class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3"></textarea>
                    <button onclick="deleteStep(${count.value})" type="button" class="bg-veryummy-ternary text-4xl p-2 rounded-sm align-middle my-auto">
                        <x-fas-trash-alt class="text-white h-5 w-5" />
                    </button>
                </div>`

            // On ajoute juste avant la fin du parent la nouvelle étape
            document.getElementById("steps").insertAdjacentHTML('beforeEnd', newStep);
        }
        // Suppression d'un ingrédient
        function deleteIngredient(place) {
            var ingredientToDelete = document.getElementById("ingredientInputs" + place)
            ingredientToDelete.remove()
        }

        // Suppression d'une étape
        function deleteStep(place) {
            var stepToDelete = document.getElementById("stepInputs" + place)
            stepToDelete.remove()
        }

        // validation du formulaire
        function submitCreation() {
            let submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.classList.remove('bg-veryummy-primary');
            submitButton.classList.add('bg-orange-400');
            submitButton.classList.add('text-white');
            submitButton.textContent = "Validation de la recette en cours...";
            document.getElementById("create-form").submit();
        }


        // Modification de la photo
        var loadFile = function(event) {
            var image = document.getElementById('photo');
            image.src = URL.createObjectURL(event.target.files[0]);
        };
    </script>

</head>

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-6xl sm:text-8xl md:text-9xl w-full text-center">NOUVELLE RECETTE
            </h1>
        </div>

        @if ($errors->any())

            <ul class="mt-3 list-disc list-inside text-red-600 text-4xl">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form id="create-form" action="{{ route('my-recipes.create') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('PUT')
            {{-- Nom de la recette --}}
            <div class="w-3/4 lg:w-1/2 mb-3 px-4 mx-auto">
                <input type="text" placeholder="NOM DE LA RECETTE" name="nom" value="{{ old('nom') }}"
                    class="caret-gray-400 text-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
            </div>
            <div class="flex flex-wrap justify-center mb-5 lg:mt-12 xl:mt-3">
                {{-- Photo --}}
                <div class="w-4/5 lg:w-2/5 xl:w-7/12 mb-3 px-4 mx-auto" id="photo-div">
                    <label for="photo-input">
                        <img class="w-full h-full max-h-full object-cover rounded-sm mb-2 cursor-pointer" id="photo"
                            src="{{ asset('img/full/ajout-photo.png') }}" alt="test">
                    </label>
                    <input id="photo-input" type="file" accept=".png, .jpg, .jpeg, .avif" name="photoInput"
                        onchange="loadFile(event)" style="display: none;" />
                </div>
                <div class="w-5/6 lg:w-3/5 xl:w-5/12 my-auto">
                    {{-- Type de recette --}}
                    <div class="flex flex-wrap mb-3 px-4 justify-center items-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">TYPE</span></div>
                        <div class="my-auto">
                            <select name="type"
                                class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('type') == $type->id ? 'selected' : '' }}>{{ $type->name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Préparation --}}
                    <div class="flex flex-wrap mb-3 px-4 justify-center items-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">PREPARATION</span>
                        </div>
                        <div class="my-auto">
                            <input type="number" name="preparation" min="0" step="1"
                                value="{{ old('preparation') }}"
                                class="text-gray-400 caret-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <span class="text-gray-400 text-5xl">MINUTES</span>
                        </div>
                    </div>
                    {{-- Cuisson --}}
                    <div class="flex flex-wrap mb-3 px-4 justify-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">CUISSON</span>
                        </div>
                        <div class="my-auto">
                            <input type="number" name="cuisson" min="0" step="1"
                                value="{{ old('cuisson') }}"
                                class="text-gray-400 caret-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <span class="text-gray-400 text-5xl">MINUTES</span>
                        </div>
                    </div>
                    {{-- Personnes --}}
                    <div class="flex flex-wrap mb-8 px-4 justify-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">PARTS</span>
                        </div>
                        <div class="my-auto">
                            <input type="number" name="parts" min="1" step="1" max="20"
                                value="{{ old('parts') }}"
                                class="text-gray-400 caret-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <span class="text-gray-400 text-5xl">PERSONNES</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Résumé --}}
            {{-- Ingrédients --}}
            <div class="h-14 text-veryummy-secondary text-7xl w-full text-center mb-7">INGREDIENTS</div>

            <div id="ingredients" class=" divide-y-4 divide-dotted divide-gray-400 divide">

                @if (old('ingredients'))
                    @foreach (old('ingredients') as $ingredientK => $ingredientV)
                        <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 mx-auto justify-center pt-8"
                            id="ingredientInputs{{ $ingredientK }}">

                            {{-- AUTOCOMPLETE --}}
                            <div class="autocomplete">
                                <input type="hidden" name="ingredients[{{ $ingredientK }}][ingredientId]"
                                    value="0" id="ingredientId{{ $ingredientK }}">
                                <input id="ingredient{{ $ingredientK }}" type="text" value="{{$ingredientV['ingredientName']}}"
                                    name="ingredients[{{ $ingredientK }}][ingredientName]" placeholder="INGREDIENT"
                                    class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                            </div>
                            <div class="text-center">
                                <input type="number" name="ingredients[{{ $ingredientK }}][ingredientQuantity]"
                                    min="0" step="1" value="{{$ingredientV['ingredientQuantity']}}"
                                    class="caret-gray-400 border-gray-100 border-2 text-gray-400 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                                <select name="ingredients[{{ $ingredientK }}][ingredientUnit]"
                                    class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $unit->id == $ingredientV['ingredientUnit'] ? 'selected' : '' }}>
                                            {{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    class="bg-gray-400 text-4xl p-2 rounded-sm disabled align-middle ml-2">
                                    <x-fas-trash-alt class="text-white h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 mx-auto justify-center "
                        id="ingredientInputs0">

                        {{-- AUTOCOMPLETE --}}
                        <div class="autocomplete">
                            <input type="hidden" name="ingredients[0][ingredientId]" value="0"
                                id="ingredientId0">
                            <input id="ingredient0" type="text" name="ingredients[0][ingredientName]"
                                placeholder="INGREDIENT"
                                class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                        </div>
                        <div class="text-center">
                            <input type="number" name="ingredients[0][ingredientQuantity]" min="0"
                                class="caret-gray-400 border-gray-100 border-2 text-gray-400 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <select name="ingredients[0][ingredientUnit]"
                                class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                class="bg-gray-400 text-4xl p-2 rounded-sm disabled align-middle ml-2">
                                <x-fas-trash-alt class="text-white h-5 w-5" />
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            {{-- Ajouter un ingrédient --}}
            <div class="w-full text-center mb-10">
                <button type="button" class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                        onclick="insertIngredient()">
                        AJOUTER UN INGREDIENT</span></button>
            </div>
            <input type="hidden" name="ingredientCount"
                value="{{ !empty(old('ingredients')) ? count(old('ingredients')) - 1 : 0 }}" id="ingredientCount">


            {{-- Etapes --}}
            <div class="h-14 text-veryummy-secondary text-7xl w-full text-center mb-7">ETAPES</div>

            <div id="steps" class=" divide-y-4 divide-dotted divide-gray-400 divide">
                @if (old('steps'))
                    @foreach (old('steps') as $stepK => $stepV)
                        <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 mx-auto justify-center pt-8"
                            id="stepInputs{{ $stepK }}">
                            <textarea type="text" placeholder="ETAPE" name="steps[{{ $stepK }}][stepDescription]"
                                class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3">{{ $stepV['stepDescription'] }}</textarea>
                            <button type="button"
                                class="bg-gray-400 text-4xl p-2 rounded-sm disabled align-middle my-auto">
                                <x-fas-trash-alt class="text-white h-5 w-5" />
                            </button>
                        </div>
                    @endforeach
                @else
                    <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 mx-auto justify-center pt-8"
                        id="stepInputs0">
                        <textarea type="text" placeholder="ETAPE" name="steps[0][stepDescription]"
                            class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3"></textarea>
                        <button type="button"
                            class="bg-gray-400 text-4xl p-2 rounded-sm disabled align-middle my-auto">
                            <x-fas-trash-alt class="text-white h-5 w-5" />
                        </button>
                    </div>
                @endif
            </div>
            {{-- Ajouter une étape --}}
            <div class="w-full text-center mb-10">
                <button type="button" class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                        onclick="insertStep()">
                        AJOUTER UNE ETAPE</span></button>
            </div>
            <input type="hidden" name="stepCount"
                value="{{ !empty(old('steps')) ? count(old('steps')) - 1 : 0 }}" id="stepCount">

            {{-- Validation du formulaire --}}
            <div class="w-4/5 lg:w-1/2 text-center mb-10 mx-auto">
                <button id="submitButton"  onclick="submitCreation()" class="bg-veryummy-secondary text-4xl p-2 rounded-sm w-full"><span class="text-white">
                        CREER LA RECETTE</span></button>
            </div>

        </form>
    </div>

    {{-- Chargement du script d'auto completion --}}
    <script src="{{ asset('js/auto-complete.js') }}"></script>
    <script>
        //Chargement des ingrédients
        var ingredients = {{ Illuminate\Support\Js::from($ingredients) }};

        autocomplete(document.getElementById("ingredient0"), ingredients, 0);
    </script>
</body>

</html>
