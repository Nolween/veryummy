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
@php
// dd($users[0]);
@endphp
<script>
    function closeDialogDeletion() {
        let modal = document.getElementById("ban-overlay");
        modal.classList.add('hidden');
        let useridInput = document.getElementById("user-id-input");
        useridInput.value = null;
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
            <a href="{{ route('admin-ingredients.index', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">INGREDIENTS</button>
            </a>
            <a href="{{ route('admin-recipes.index', 0) }}">
                <button type="button"
                    class="bg-veryummy-secondary text-5xl text-white py-2 px-5 w-56 mb-5">RECETTES</button>
            </a>
            <button type="button"
                class="bg-veryummy-primary text-5xl text-white py-2 px-5 w-56 mb-5">UTILISATEURS</button>
        </div>
        {{-- Formulaire --}}
        <form action="{{ route('admin-users.index', $typeList) }}" method="GET">
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
                    <a href="{{ route('admin-users.index', 0) }}"><button type="button"
                            class="{{ $typeList == 0 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary' }} text-4xl w-28 p-2 rounded-sm"><span
                                class="text-white" id="registration-button">
                                SIGNALES</span></button></a>
                    <a href="{{ route('admin-users.index', 1) }}"><button type="button"
                            class="{{ $typeList == 1 ? 'bg-veryummy-primary' : 'bg-veryummy-secondary' }} text-4xl w-28 p-2 rounded-sm"><span
                                class="text-white" id="registration-button">
                                TOUS</span></button></a>
                </div>

            </div>
        </form>

        <div class="flex flex-wrap justify-center w-full text-center">
            {{-- NOTIFICATION --}}
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
            @if (session('deletionSuccess'))
                <div class=" text-center bg-veryummy-primary text-white text-3xl w-full lg:w-1/2 mx-2 p-2 mb-2">
                    {{ session('deletionSuccess') }}</div>
            @endif
        </div>
        {{-- Pagination --}}
        <div class="flex justify-center mb-5">
            {{ $users->links() }}
        </div>
        {{-- Eléments --}}
        {{-- Formulaire --}}
        <form method="POST" action="{{ route('admin-users.moderate') }}" id="opinion-deletion-form">
            @method('DELETE')
            @csrf
            <input id="opinion-id-input" type="hidden" value="0" name="opinionid">
            <input id="destroy-input" type="hidden" value="0" name="destroy">
            <input id="typelist" type="hidden" value="0" name="typelist" value="{{ $typeList }}">
            <div class="flex flex-wrap justify-center">
                @foreach ($users as $userK => $userV)
                    <x-elements.user-report :place="$userK" :name="$userV->name" :date="$userV->created_at" :reportscount="$userV->reported_opinions_by_other_count"
                        :opinions="$userV->opinions" :userid="$userV->id" />
                @endforeach
            </div>
        </form>
        {{-- Pagination --}}
        <div class="flex justify-center mb-5">
            {{ $users->links() }}
        </div>
    </div>

    {{-- OVERLAY POUR LA CONFIRMATION DE BANISSEMENT DE COMPTE --}}
    <div class="h-screen bg-black bg-opacity-50 fixed inset-0 z-50 hidden justify-center flex items-center"
        id="ban-overlay">

        <div class=" bg-white rounded-sm block w-3/4 inset-0 px-2">
            <div class="flex justify-between items-center"><span
                    class="md:pl-32 lg:pl-60 xl:pl-80 text-3xl sm:text-5xl md:text-6xl text-veryummy-secondary pl-3">BANISSEMENT
                    DU COMPTE</span>
                <span class="cursor-pointer">
                    <x-far-window-close onclick="closeDialogDeletion()"
                        class="text-veryummy-secondary bg-white pr-3 pb-2" />
                </span>
            </div>

            <div class="text-center text-4xl text-veryummy-ternary mb-7">
                <p>Voulez-vous vraiment bannir le compte?</p>
            </div>
            {{-- Formulaire --}}
            <form method="POST" action="{{ route('admin-users.ban') }}">
                @method('PATCH')
                @csrf
                <input id="user-id-input" type="hidden" value="0" name="userid">
                <input id="typelist" type="hidden" value="0" name="typelist" value="{{ $typeList }}">
                @error('ban-account-password')
                    <div class="w-full text-center text-veryummy-ternary text-3xl">{{ $message }}</div>
                @enderror
                <div class="flex flex-wrap justify-center sm:justify-between mb-5">
                    <button onclick="closeDialogDeletion()"
                        class="mx-3 text-4xl px-5 py-2 text-white bg-gray-400 mb-3">ANNULER</button>
                    <button class="mx-3 text-4xl px-5 py-2 text-white bg-veryummy-ternary mb-3">BANNIR</button>
                </div>
            </form>
        </div>

    </div>
</body>

</html>
