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
                // dd($recipe['ingredients']);
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
                <textarea type="text" placeholder="ETAPE" name="step[0][description]"
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
        function submitEdition() {
            let submitButton = document.getElementById('submitButton');
            submitButton.disabled = true;
            submitButton.classList.remove('bg-veryummy-primary');
            submitButton.classList.add('bg-orange-400');
            submitButton.classList.add('text-white');
            submitButton.textContent = "Validation de la recette en cours...";
            document.getElementById("edition-form").submit();
        }

        // Modification de la photo
        var loadFile = function(event) {
            var image = document.getElementById('photo');
            image.src = URL.createObjectURL(event.target.files[0]);
        };
    </script>
</head>

@php
// dd($recipe->steps);
// $ingredients = [['name' => 'Pommes', 'unit' => 1, 'quantity' => 5], ['name' => 'Poires', 'unit' => 1, 'quantity' => 4], ['name' => 'Scoubidoubidou', 'unit' => 3, 'quantity' => 3]];
// $steps = ['Prenez les pommes', 'Prenez les poires', 'Prenez le scoubidoubidou', 'Mélangez tout', 'Servez frais', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Maiores doloribus, alias at id expedita natus earum. Perspiciatis dolor voluptates voluptate ad neque rem, accusantium magni commodi facere laborum at soluta.'];
@endphp

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-6xl sm:text-8xl md:text-9xl w-full text-center">
                {!! $recipe->name !!}</h1>
        </div>

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
        <form id="edition-form" action="{{ route('my-recipes.update') }}" enctype="multipart/form-data" method="POST">
            @csrf
            @method('POST')
            <input type="hidden" name="recipeid" value="{{ $recipe->id }}">
            {{-- Nom de la recette --}}
            <div class="w-3/4 lg:w-1/2 mb-3 px-4 mx-auto">
                <input type="text" placeholder="NOM DE LA RECETTE" name="nom" value="{!! old('nom') ?? $recipe->name !!}"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm text-gray-400 focus:border-gray-400 focus:outline-none">
            </div>
            <div class="flex flex-wrap justify-center mb-5">
                {{-- Photo --}}
                <div class="w-3/4 lg:w-1/2 mb-3 px-4 mx-auto" id="photo-div">
                    <label for="photo-input">
                        <img class="w-full h-full object-cover rounded-sm mb-2 cursor-pointer" id="photo"
                            src="{{ storage_path('app/public/img/full/' . $recipe->image) }}" alt="photo">
                    </label>
                    <input id="photo-input" type="file" accept=".png, .jpg, .jpeg, .avif" name="photoInput"
                        id="photo-input" onchange="loadFile(event)" style="display: none;" />
                </div>

                <div class="w-4/5 lg:w-2/5 xl:w-5/12 my-auto">
                    {{-- Type --}}
                    <div class="flex flex-wrap mb-3 px-4 justify-center items-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">TYPE</span>
                        </div>
                        <div class="my-auto">
                            <select name="type"
                                class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                                @if (old('type'))
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" {{ $type->id == old('type') ? 'selected' : '' }}>{{ $type->name }}
                                            </option>
                                    @endforeach
                                @else
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" {{ $type->id == $recipe->recipe_type_id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                            </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    {{-- Préparation --}}
                    <div class="flex flex-wrap mb-3 px-4 justify-center items-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">PREPARATION</span>
                        </div>
                        <div class="my-auto">
                            <input min="0" step="1" type="number" name="preparation"
                                value="{{ old('preparation') ?? $recipe->making_time }}"
                                class="caret-gray-400 text-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <span class="text-gray-400 text-5xl">MINUTES</span>
                        </div>
                    </div>
                    {{-- Cuisson --}}
                    <div class="flex flex-wrap mb-3 px-4 justify-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">CUISSON</span>
                        </div>
                        <div class="my-auto">
                            <input min="0" step="1" type="number" name="cuisson"
                                value="{{ old('cuisson') ?? $recipe->cooking_time }}"
                                class="caret-gray-400 text-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <span class="text-gray-400 text-5xl">MINUTES</span>
                        </div>
                    </div>
                    {{-- Personnes --}}
                    <div class="flex flex-wrap mb-8 px-4 justify-center sm:text-right xs:text-center">
                        <div class="w-40"><span class="text-veryummy-primary text-5xl">PARTS</span>
                        </div>
                        <div class="my-auto">
                            <input type="number" name="parts" min="1" step="1" max="20"
                                value="{{ old('parts') ?? $recipe->servings }}"
                                class="caret-gray-400 text-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                            <span class="text-gray-400 text-5xl">PERSONNES</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Résumé --}}
            {{-- Ingrédients --}}
            <div class="h-14 text-veryummy-secondary text-7xl w-full text-center mb-7">INGREDIENTS</div>

            <div id="ingredients" class=" divide-y-4 divide-dotted divide-gray-400 divide">
                {{-- Si l'utisateur a fait des modifications et qu'il y a une erreur --}}
                @if (old('ingredients'))
                    @foreach (old('ingredients') as $ingredientK => $ingredientV)
                        <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 pt-4 mx-auto justify-center"
                            id="ingredientInputs{{ $ingredientK }}">

                            <div class="autocomplete">
                                <input type="hidden" name="ingredients[{{ $ingredientK }}][ingredientId]"
                                    value="{{ $ingredientV['ingredientId'] }}"
                                    id="ingredientId{{ $ingredientK }}">
                                <input type="text" placeholder="INGREDIENT" id="ingredient{{ $ingredientK }}"
                                    name="ingredients[{{ $ingredientK }}][ingredientName]"
                                    value="{{ $ingredientV['ingredientName'] }}"
                                    class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                            </div>
                            <div class="text-center">
                                <select name="ingredients[{{ $ingredientK }}][ingredientUnit]"
                                    class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $ingredientV['ingredientUnit'] == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="ingredients[{{ $ingredientK }}][ingredientQuantity]"
                                    value="{{ $ingredientV['ingredientQuantity'] }}"
                                    class="caret-gray-400 border-gray-100 border-2 text-gray-400 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                                <button
                                    {{ $ingredientK !== 0 ? 'onclick=deleteIngredient(' . $ingredientK . ')' : '' }}
                                    type="button"
                                    class=" text-4xl p-2 rounded-sm disabled align-middle {{ $ingredientK === 0 ? 'disabled bg-gray-400' : 'bg-veryummy-ternary' }}">
                                    <x-fas-trash-alt class="text-white h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach ($recipe['ingredients'] as $ingredientK => $ingredientV)
                        <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 pt-4 mx-auto justify-center"
                            id="ingredientInputs{{ $ingredientK }}">

                            <div class="autocomplete">
                                <input type="hidden" name="ingredients[{{ $ingredientK }}][ingredientId]"
                                    value="{{ $ingredientV->id }}" id="ingredientId{{ $ingredientK }}">
                                <input type="text" placeholder="INGREDIENT" id="ingredient{{ $ingredientK }}"
                                    name="ingredients[{{ $ingredientK }}][ingredientName]"
                                    value="{{ $ingredientV->ingredient->name }}"
                                    class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                            </div>
                            <div class="text-center">
                                <select name="ingredients[{{ $ingredientK }}][ingredientUnit]"
                                    class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ $ingredientV->unit_id == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="ingredients[{{ $ingredientK }}][ingredientQuantity]"
                                    value="{{ $ingredientV->quantity }}"
                                    class="caret-gray-400 border-gray-100 border-2 text-gray-400 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                                <button
                                    {{ $ingredientK !== 0 ? 'onclick=deleteIngredient(' . $ingredientK . ')' : '' }}
                                    type="button"
                                    class=" text-4xl p-2 rounded-sm disabled align-middle {{ $ingredientK === 0 ? 'disabled bg-gray-400' : 'bg-veryummy-ternary' }}">
                                    <x-fas-trash-alt class="text-white h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            {{-- Ajouter un ingrédient --}}
            <div class="w-full text-center mb-10">
                <button type="button" class="bg-veryummy-primary text-4xl py-2 px-4 rounded-sm"><span
                        class="text-white" onclick="insertIngredient()">
                        AJOUTER UN INGREDIENT</span></button>
            </div>
            <input type="hidden" name="ingredientCount" value="{{ count($recipe['ingredients']) + 1 }}"
                id="ingredientCount">


            {{-- Etapes --}}
            <div class="h-14 text-veryummy-secondary text-7xl w-full text-center mb-7">ETAPES</div>

            <div id="steps" class=" divide-y-4 divide-dotted divide-gray-400 divide">
                {{-- Si l'utisateur a fait des modifications et qu'il y a une erreur --}}
                @if (old('steps'))
                    @foreach (old('steps') as $stepK => $stepV)
                        <div class="w-full xl:w-3/4 flex flex-wrap my-4 pt-4 px-4 mx-auto justify-center"
                            id="stepInputs{{ $stepK }}">
                            <textarea type="text" placeholder="ETAPE" name="steps[{{ $stepK }}][stepDescription]"
                                class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3">{{ $stepV['stepDescription'] }}</textarea>
                            <button {{ $stepK !== 0 ? 'onclick=deleteStep(' . $stepK . ')' : '' }} type="button"
                                class="text-4xl p-2 rounded-sm disabled align-middle my-auto {{ $stepK === 0 ? 'disabled bg-gray-400' : 'bg-veryummy-ternary' }}">
                                <x-fas-trash-alt class="text-white h-5 w-5" />
                            </button>
                        </div>
                    @endforeach
                @else
                    @foreach ($recipe->steps as $stepK => $stepV)
                        <div class="w-full xl:w-3/4 flex flex-wrap my-4 pt-4 px-4 mx-auto justify-center"
                            id="stepInputs{{ $stepK }}">
                            <textarea type="text" placeholder="ETAPE" name="steps[{{ $stepK }}][stepDescription]"
                                class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3">{{ $stepV->description }}</textarea>
                            <button {{ $stepK !== 0 ? 'onclick=deleteStep(' . $stepK . ')' : '' }} type="button"
                                class="text-4xl p-2 rounded-sm disabled align-middle my-auto {{ $stepK === 0 ? 'disabled bg-gray-400' : 'bg-veryummy-ternary' }}">
                                <x-fas-trash-alt class="text-white h-5 w-5" />
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
            {{-- Ajouter une étape --}}
            <div class="w-full text-center mb-10">
                <button type="button" class="bg-veryummy-primary text-4xl py-2 px-4 rounded-sm"><span
                        class="text-white" onclick="insertStep()">
                        AJOUTER UNE ETAPE</span></button>
            </div>
            <input type="hidden" name="stepCount" value="{{ count($recipe->steps) + 1 }}" id="stepCount">

            {{-- Validation du formulaire --}}
            <div class="w-4/5 lg:w-1/2 text-center mb-10 mx-auto">
                <button id="submitButton" onclick="submitEdition()" class="bg-veryummy-secondary text-4xl p-2 rounded-sm w-full"><span class="text-white">
                        EDITER LA RECETTE</span></button>
            </div>
        </form>
    </div>

    {{-- Chargement du script d'auto completion --}}
    <script src="{{ asset('js/auto-complete.js') }}"></script>
    <script>
        //Chargement des ingrédients
        var ingredients = {{ Illuminate\Support\Js::from($ingredientsList) }};
        @foreach ($recipe['ingredients'] as $ingredientK => $ingredientV)
            {
                autocomplete(document.getElementById('ingredient' + {{ $ingredientK }}), ingredients,
                    {{ $ingredientK }}, {{ Illuminate\Support\Js::from(route('new-ingredient.show')) }});
            }
        @endforeach
    </script>
</body>

</html>
