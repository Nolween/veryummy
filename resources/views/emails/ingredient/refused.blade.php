@component('mail::message')
# Proposition d'ingrédient

Vous avez proposé un nouvel ingrédient {!! $informations['ingredient'] !!} afin de pouvoir l'utiliser sur le site Veryummy.
Cet ingrédient n'est malheureusement pas accepté.
    
Voici la raison de ce refus:

{!! $informations['message'] !!}

@component('mail::button', ['url' => $informations['url']])
    Aller sur le site
@endcomponent

Salutations,<br>
{{ config('app.name') }}
@endcomponent
