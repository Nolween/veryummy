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

function openDialogDeletion() {
        let modal = document.getElementById("deletion-overlay");
        modal.classList.remove('hidden');
    }
    function closeDialogDeletion() {
        let modal = document.getElementById("deletion-overlay");
        modal.classList.add('hidden');
    }
</script>

<body class="antialiased">
    <div id="entire-page">
        {{-- Menu de navigation --}}
        <x-navigation.menu />
        {{-- Titre de la page --}}
        <div class="mb-4 pt-20 sm:pt-10">
            <h1 class="text-veryummy-secondary text-7xl sm:text-9xl w-full text-center">MON COMPTE</h1>
        </div>

        {{-- Formulaire --}}
        <form action="POST" action="/account">
            @method('PUT')

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
                <div class="text-center mb-5 w-full">
                    <button class="bg-veryummy-primary text-4xl p-2 rounded-sm px-7" ><span class="text-white"
                            id="edition-button">
                            MODIFICATION</span></button>
                </div>
                <div class="text-center mb-5 w-full">
                    <button type="button"class="bg-veryummy-ternary text-4xl p-2 rounded-sm px-7" onclick="openDialogDeletion()"><span class="text-white"
                            id="deletion-button">
                            SUPPRIMER MON COMPTE</span></button>
                </div>
            </div>

        </form>

        {{-- OVERLAY POUR LA MODAL DE CONNEXION --}}
        <div class="h-screen bg-black bg-opacity-50 fixed inset-0 z-50 hidden flex justify-center items-center"
            id="deletion-overlay">

            <div class=" bg-white rounded-sm block w-3/4 inset-0 px-2">
                <div class="flex justify-between items-center"><span
                        class="md:pl-32 lg:pl-60 xl:pl-80 text-3xl sm:text-5xl md:text-6xl text-veryummy-secondary pl-3">SUPPRESSION DU COMPTE</span>
                    <span class="cursor-pointer">
                        <x-far-window-close onclick="closeDialogDeletion()"
                            class="text-veryummy-secondary bg-white pr-3 pb-2" />
                    </span>
                </div>

                <div class="text-center text-4xl text-veryummy-ternary mb-7">
                    <p>Voulez-vous vraiment supprimer votre compte?</p>
                    <p>Toutes vos informations seront perdues</p>
                </div>
                <div class="flex flex-wrap justify-center sm:justify-between mb-5">
                    <button onclick="closeDialogDeletion()" class="mx-3 text-4xl px-5 py-2 text-white bg-gray-400 mb-3">ANNULER</button>
                    <button class="mx-3 text-4xl px-5 py-2 text-white bg-veryummy-ternary mb-3">SUPPRIMER</button>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
