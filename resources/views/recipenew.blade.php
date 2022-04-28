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
    <script>
        // Ajout d'un nouvel ingrédient
        function insertIngredient() {
            var count = document.getElementById("ingredientCount")
            count.value = parseInt(count.value) + 1

            var newIngredient = `
            <div class="w-full xl:w-3/4 flex flex-wrap my-4 pt-8 px-4 mx-auto justify-center" id="ingredientInputs` +
                count
                .value + `">
                <input type="text" placeholder="INGREDIENT" name="ingredient[` + count.value + `][name]"
                    class="caret-gray-400 text-gray-400 border-gray-100 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                <div class="text-center">
                    <select name="ingredient[` + count.value + `][unit]"
                        class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                        <option value="1">UNITE(S)</option>
                        <option value="2">CUILLERE(S) A SOUPE</option>
                        <option value="3">CUILLERE(S) A CAFE</option>
                        <option value="4">CENTILITRE(S)</option>
                        <option value="5">LITRE(S)</option>
                    </select>
                    <input type="number" name="ingredient[` + count.value + `][quantity]"
                        class="caret-gray-400 border-gray-100 border-2 text-4xl text-gray-400 w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                    <button onclick="deleteIngredient(` + count.value + `)" type="button" class="bg-veryummy-ternary text-5xl p-2 rounded-sm  align-middle">
                        <x-fas-trash-alt class="text-white h-6 w-6"/>
                    </button>
                </div>
            </div>`

            // On ajoute juste avant la fin du parent le nouvel ingrédient
            document.getElementById("ingredients").insertAdjacentHTML('beforeEnd', newIngredient);
        }
        // Ajout d'une nouvelle étape
        function insertStep() {
            var count = document.getElementById("stepCount")
            count.value = parseInt(count.value) + 1

            var newStep = `
            <div class="w-full xl:w-3/4 flex flex-wrap my-4 pt-8 px-4 mx-auto justify-center" id="stepInputs` + count.value + `">
                <textarea type="text" placeholder="ETAPE" name="step[0][description]"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3"></textarea>
                <button onclick="deleteStep(` + count.value + `)" type="button" class="bg-veryummy-ternary text-4xl p-2 rounded-sm align-middle my-auto">
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
    </script>
</head>

<body class="antialiased">
    <div>
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-6xl sm:text-8xl md:text-9xl w-full text-center">NOUVELLE RECETTE</h1>
        </div>
        {{-- Nom de la recette --}}
        <div class="w-3/4 lg:w-1/2 mb-3 px-4 mx-auto">
            <input type="text" placeholder="NOM DE LA RECETTE" name="name"
                class="caret-gray-400 border-gray-100 border-2 text-4xl w-full pl-4 rounded-sm focus:border-gray-400 focus:outline-none">
        </div>
        {{-- Photo --}}
        <div class="w-3/4 lg:w-1/2 mb-3 px-4 mx-auto">
            <img class="w-full h-full max-h-80 object-cover rounded-sm mb-2"
                src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80"
                alt="test">
        </div>
        {{-- Résumé --}}
        {{-- Préparation --}}
        <div class="w-3/4 lg:w-1/2 flex flex-wrap mb-3 px-4 mx-auto justify-center">
            <div class=" text-center lg:text-left"><span class="text-veryummy-primary text-5xl">PREPARATION</span></div>
            <div class=" text-center lg:text-left my-auto">
                <input type="number" name="making"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                <span class="text-gray-400 text-5xl">MINUTES</span>
            </div>
        </div>
        {{-- Cuisson --}}
        <div class="w-3/4 lg:w-1/2 flex flex-wrap mb-8 px-4 mx-auto justify-center">
            <div class=" text-center lg:text-left"><span class="text-veryummy-primary text-5xl">CUISSON</span></div>
            <div class=" text-center lg:text-left my-auto">
                <input type="number" name="cooking"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                <span class="text-gray-400 text-5xl">MINUTES</span>
            </div>
        </div>
        {{-- Ingrédients --}}
        <div class="h-14 text-veryummy-secondary text-7xl w-full text-center mb-7">INGREDIENTS</div>

        <div id="ingredients" class=" divide-y-4 divide-dotted divide-gray-400 divide">
            <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 mx-auto justify-center" id="ingredientInputs0">
                <input type="text" placeholder="INGREDIENT" name="ingredient[0][name]"
                    class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                <div class="text-center">
                    <select name="ingredient[0][unit]"
                        class="text-gray-400 border-gray-100 border-2 text-4xl w-38 pl-4 rounded-sm focus:border-gray-400 focus:outline-none ml-2 mb-3">
                        <option value="1">UNITE(S)</option>
                        <option value="2">CUILLERE(S) A SOUPE</option>
                        <option value="3">CUILLERE(S) A CAFE</option>
                        <option value="4">CENTILITRE(S)</option>
                        <option value="5">LITRE(S)</option>
                    </select>
                    <input type="number" name="ingredient[0][quantity]"
                        class="caret-gray-400 border-gray-100 border-2 text-gray-400 text-4xl w-24 pl-4 mx-3 rounded-sm focus:border-gray-400 focus:outline-none">
                    <button type="button" class="bg-gray-400 text-4xl p-2 rounded-sm disabled align-middle">
                        <x-fas-trash-alt class="text-white h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
        {{-- Ajouter un ingrédient --}}
        <div class="w-full text-center mb-10">
            <button type="button" class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                    onclick="insertIngredient()">
                    AJOUTER UN INGREDIENT</span></button>
        </div>
        <input type="hidden" name="ingredientCount" value="0" id="ingredientCount">


        {{-- Etapes --}}
        <div class="h-14 text-veryummy-secondary text-7xl w-full text-center mb-7">ETAPES</div>

        <div id="steps" class=" divide-y-4 divide-dotted divide-gray-400 divide">
            <div class="w-full xl:w-3/4 flex flex-wrap my-4 px-4 mx-auto justify-center" id="stepInputs0">
                <textarea type="text" placeholder="ETAPE" name="step[0][description]"
                    class="caret-gray-400 border-gray-100 border-2 text-4xl w-3/4 pl-4 text-gray-400 rounded-sm focus:border-gray-400 focus:outline-none mb-3 mx-3"></textarea>
                <button type="button" class="bg-gray-400 text-4xl p-2 rounded-sm disabled align-middle my-auto">
                    <x-fas-trash-alt class="text-white h-5 w-5" />
                </button>
            </div>
        </div>
        {{-- Ajouter une étape --}}
        <div class="w-full text-center mb-10">
            <button type="button" class="bg-veryummy-primary text-4xl p-2 rounded-sm"><span class="text-white"
                    onclick="insertStep()">
                    AJOUTER UNE ETAPE</span></button>
        </div>
        <input type="hidden" name="stepCount" value="0" id="stepCount">

        {{-- Validation du formulaire --}}
        <div class="w-4/5 lg:w-1/2 text-center mb-10 mx-auto">
            <button type="button" class="bg-veryummy-secondary text-4xl p-2 rounded-sm w-full"><span class="text-white"
                    onclick="insertStep()">
                    CREER LA RECETTE</span></button>
        </div>

    </div>
</body>

</html>
