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
    // Activation du bouton de validation de formulaire si acceptation des règles
    function setValidButton() {
        const button = document.getElementById('inscription-button');
        // Tailwind sépare le button en 2 éléments, il faut donc aussi modifier le parent.
        const button2 = button.parentNode;
        const checkbox = document.getElementById('rules');
        if (checkbox.checked) {
            button2.removeAttribute('disabled')
            button.classList.remove("bg-gray-500");
            button2.classList.remove("bg-gray-500");
            button.classList.add("bg-veryummy-secondary");
            button2.classList.add("bg-veryummy-secondary");
        } else {
            button2.disabled = true;
            button.classList.add("bg-gray-500");
            button2.classList.add("bg-gray-500");
            button.classList.remove("bg-veryummy-secondary");
            button2.classList.remove("bg-veryummy-secondary");
        }
    }
</script>

<body class="antialiased">
    <div id="entire-page">
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-7xl sm:text-9xl w-full text-center">INSCRIPTION</h1>
        </div>

        {{-- Formulaire --}}
        <form action="POST">
            @method('POST')

            <div class=" bg-white rounded-sm justify-center flex flex-wrap">
                <div class="w-full mx-auto justify-center text-center mb-5 px-3 md:px-0">
                    <input placeholder="MAIL" type="email" name="mail"
                        class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full  md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="w-full mx-auto justify-center text-center mb-5 px-3 md:px-0">
                    <input placeholder="PSEUDO" type="text" name="login"
                        class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full  md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="w-full mx-auto justify-center text-center mb-5 px-3 md:px-0">
                    <input placeholder="MOT DE PASSE" type="password" name="password"
                        class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="w-full mx-auto justify-center text-center mb-5 px-3 md:px-0">
                    <input placeholder="CONFIRMATION" type="password" name="confirmation"
                        class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="w-full mx-auto justify-center text-center mb-5 px-3 md:px-0">
                    <textarea type="password" name="confirmation" disabled
                        class="h-80 caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">Lorem ipsum dolor sit amet consectetur adipisicing elit. Explicabo assumenda aperiam deserunt officia, veritatis architecto velit! Animi minima voluptas veritatis aut nam dolor unde quia totam perspiciatis. Ut, quae amet! Lorem, ipsum dolor sit amet consectetur adipisicing elit. Vero, aperiam. Similique dolorum dolores tenetur sapiente ab fugiat laborum, labore incidunt magni molestiae accusamus rem laboriosam excepturi aspernatur amet inventore esse?</textarea>
                </div>
                <div class="w-full mx-auto justify-center text-center mb-5 px-3 md:px-0">
                    <input id="rules" type="checkbox" name="rules" onchange="setValidButton()" value="true"
                        class="h-5 w-5 rounded-full accent-veryummy-primary checked:bg-gray-300 cursor-pointer">
                    <label for="rules" id="rules"><span class="text-3xl text-gray-400 pl-2 cursor-pointer">J'ai lu et j'accepte les règles
                            de
                            la charte ci-dessus.</span></label>

                </div>
                <div class="text-center mb-5">
                    <button class="bg-gray-500 text-4xl p-2 rounded-sm" disabled><span class="text-white"
                            id="inscription-button">
                            INSCRIPTION</span></button>
                </div>
            </div>

        </form>
    </div>
</body>

</html>
