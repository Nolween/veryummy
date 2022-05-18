@component('mail::message')
    # Proposition d'ingrédient

    Vous avez proposé un nouvel ingrédient {{ $informations['ingredient'] }} afin de pouvoir l'utiliser sur le site Veryummy.
    Cet ingrédient est accepté !
    
    @component('mail::button', ['url' => $informations['url']])
        Aller sur le site
    @endcomponent

    Merci à vous!

    {{ config('app.name') }}
@endcomponent
