@component('mail::message')
    # Désactivation de l'une de vos recettes

    Suite à de nombreux signalements, votre recette {{ $informations['recipe'] }} a été désactivée sur le site Veryummy.
    Veuillez noter qu'un trop grand nombre de signalements justifié de vos création pourra mener à la suppression de votre compte.
    
    @component('mail::button', ['url' => $informations['url']])
        Aller sur le site
    @endcomponent

    Salutations,
    {{ config('app.name') }}
@endcomponent
