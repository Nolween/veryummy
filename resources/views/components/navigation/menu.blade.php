<div class="drop-shadow-md z-40 fixed w-full text-4xl bg-white  text-veryummy-primary flex flex-wrap text-center mb-4">
    <div class="basis-1/3 sm:flex-1"><a href="{{ route('home') }}" class="cursor-pointer">ACCUEIL</a></div>
    <div class="basis-1/3 sm:flex-1"><a href="{{ route('exploration.list') }}" class="cursor-pointer">EXPLORATION</a>
    </div>
    @guest
        <div class="basis-1/3 sm:flex-1"><span class="cursor-pointer" onclick="openDialogConnexion()">CONNEXION</span></div>
    @endguest
    @auth
        <div class="basis-1/3 sm:flex-1"><a href="{{ route('my-notebook.list') }}" class="cursor-pointer">MON CARNET</a>
        </div>
        <div class="basis-1/3 sm:flex-1"><a href="{{ route('myaccount') }}" class="cursor-pointer">MON COMPTE</a></div>
        <div class="basis-1/3 sm:flex-1"><span class="cursor-pointer"><a href="{{ route('admin-ingredientslist') }}"
                    class="cursor-pointer">ADMIN</a></span></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="basis-1/3 sm:flex-1"><span class="cursor-pointer">DECONNEXION</span></button>
        </form>
    @endauth

</div>
@guest

    {{-- OVERLAY POUR LA MODAL DE CONNEXION, apparait aussi en cas d'erreur formulaire --}}
    <div class="h-screen bg-black bg-opacity-50 fixed inset-0 z-50 flex justify-center items-center {{ $errors->any() ? '' : 'hidden' }}"
        id="connexion-overlay">

        <div class=" bg-white rounded-sm block w-3/4 inset-0 ">
            <div class="flex justify-between items-center"><span
                    class="md:pl-32 lg:pl-60 xl:pl-80 text-5xl md:text-8xl text-veryummy-secondary pl-3">CONNEXION</span>
                <span class="cursor-pointer">
                    <x-far-window-close onclick="closeDialogConnexion()"
                        class="text-veryummy-secondary bg-white pr-3 pb-2" />
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
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="w-full mx-auto justify-center text-center mb-5">
                    <input placeholder="MAIL" type="email" name="email"
                        class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="w-full mx-auto justify-center text-center mb-5">
                    <input placeholder="MOT DE PASSE" type="password" name="password"
                        class="caret-gray-400 border-gray-100 text-gray-400 border-2 text-4xl w-full md:w-1/2 pl-4 rounded-sm focus:border-gray-400 focus:outline-none mb-3">
                </div>
                <div class="text-center mb-5">
                    <button class="bg-veryummy-secondary text-4xl p-2 rounded-sm w-1/2"><span class="text-white">
                            CONNEXION</span></button>
                </div>
            </form>
            <div class="text-center text-7xl text-veryummy-ternary">
                <a href="{{ route('registration') }}">
                    <p>PAS ENCORE DE COMPTE?</p>
                    <p>INSCRIVEZ-VOUS ICI!</p>
                </a>
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
</script>
