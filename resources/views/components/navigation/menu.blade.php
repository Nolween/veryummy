<div
    class="drop-shadow-md z-40 fixed w-full text-4xl bg-white  text-veryummy-primary flex flex-wrap text-center mb-4 justify-center">
    <div class="px-1 basis-1/3 sm:flex-1"><a href="{{ route('home') }}" class="cursor-pointer">ACCUEIL</a></div>
    <div class="px-1 basis-1/3 sm:flex-1"><a href="{{ route('exploration.list') }}"
            class="cursor-pointer">EXPLORATION</a>
    </div>
    @guest
        <div class="px-1 basis-1/3 sm:flex-1"><span class="cursor-pointer" onclick="openDialogConnexion()">CONNEXION</span>
        </div>
    @endguest
    @auth
        <div class="px-1 basis-1/3 sm:flex-1"><a href="{{ route('my-notebook.list') }}" class="cursor-pointer">MON
                CARNET</a>
        </div>
        <div class="px-1 basis-1/3 sm:flex-1"><a href="{{ route('my-account.edit') }}" class="cursor-pointer">MON
                COMPTE</a>
        </div>
        <div class="px-1 basis-1/3 sm:flex-1"><span class="cursor-pointer"><a
                    href="{{ route('admin-ingredients.index', 0) }}" class="cursor-pointer">ADMIN</a></span></div>
        <div class="px-1 basis-1/3 sm:flex-1">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button><span class="cursor-pointer">DECONNEXION</span></button>
            </form>
        </div>
    @endauth

</div>
@guest

    {{-- OVERLAY POUR LA MODAL DE CONNEXION, apparait aussi en cas d'erreur formulaire --}}
    <div class="h-screen bg-black bg-opacity-50 fixed inset-0 z-50 flex justify-center items-center {{ $errors->any() ? '' : 'hidden' }}"
        id="connexion-overlay">

        <div class=" bg-white rounded-sm block w-3/4 inset-0 ">
            <div class="flex justify-between items-center text-center"><span
                    class="w-full text-7xl md:text-8xl text-veryummy-secondary pl-3">CONNEXION</span>
                <span class="cursor-pointer pr-3">
                    <x-far-window-close onclick="closeDialogConnexion()"
                        class="text-veryummy-secondary bg-white pb-3" />
                </span>
            </div>
            {{-- Texte d'erreur --}}
            @if ($errors->any())
                <div>
                    <div class="font-medium text-red-600 text-center text-4xl">
                        Erreur de connexion, votre mail et votre mot de passe sont-ils corrects?
                    </div>
                </div>
            @endif
            <div class="text-center text-7xl mb-4">
                <a href="{{ route('registration') }}"><button type="button"
                        class="bg-veryummy-ternary text-4xl p-2 rounded-sm w-1/2"><span class="text-white">
                            INSCRIPTION</span></button>
                </a>
            </div>
            <div id="connexion-form">
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="w-full mx-auto justify-center text-center mb-5 px-2">
                        <input placeholder="MAIL" type="email" name="email" autocomplete="email"
                            class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    </div>
                    <div class="w-full mx-auto justify-center text-center mb-5 px-2">
                        <input placeholder="MOT DE PASSE" type="password" name="password" autocomplete="current-password"
                            class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    </div>
                    <div class="w-full mx-auto justify-center text-center mb-5 px-2">
                        <button type="button" class="bg-orange-300 text-4xl p-2 rounded-sm w-1/2" onclick="displayForgottenForm()"><span
                                class="text-white">
                                MOT DE PASSE OUBLIE</span></button>
                    </div>
                    <div class="text-center mb-5">
                        <button class="bg-veryummy-secondary text-4xl p-2 rounded-sm w-1/2"><span class="text-white">
                                CONNEXION</span></button>
                    </div>
                </form>
            </div>
            <div id="forgotten-form" class="hidden">
                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="w-full mx-auto justify-center text-center mb-5 px-2">
                        <input placeholder="MAIL" type="email" name="email" autocomplete="email"
                            class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                    </div>
                    <div class="w-full mx-auto justify-center text-center mb-5 px-2">
                        <button type="button" class="bg-orange-300 text-4xl p-2 rounded-sm w-1/2" onclick="displayConnexionForm()"><span
                                class="text-white">
                                ANNULER</span></button>
                    </div>
                    <div class="text-center mb-5">
                        <button class="bg-veryummy-secondary text-4xl p-2 rounded-sm w-1/2"><span class="text-white">
                                REINITIALISER</span></button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endguest
<script>
    function openDialogConnexion() {
        let modal = document.getElementById("connexion-overlay");
        modal.classList.remove('hidden');
    }

    function closeDialogConnexion() {
        let modal = document.getElementById("connexion-overlay");
        modal.classList.add('hidden');
    }

    function displayForgottenForm() {
        let connexionModal = document.getElementById("connexion-form");
        connexionModal.classList.add('hidden');
        let forgottenModal = document.getElementById("forgotten-form");
        forgottenModal.classList.remove('hidden');
    }
    function displayConnexionForm() {
        let connexionModal = document.getElementById("connexion-form");
        connexionModal.classList.remove('hidden');
        let forgottenModal = document.getElementById("forgotten-form");
        forgottenModal.classList.add('hidden');
    }
</script>
