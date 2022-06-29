@component('mail::message')
# Ingrédients en attente de modération
@foreach ($informations['ingredients'] as $ingredient)
    - {!! $ingredient->name !!} proposé par {!! $ingredient->user->name !!}
@endforeach
# Recettes en attente de modération
@foreach ($informations['recettes'] as $recette)
    - {!! $recette->name !!} proposé par {!! $recette->user->name !!} avec {{ $recette->opinions_count }} signalements.
@endforeach
@component('mail::button', ['url' => $informations['url']])
    Aller sur le site
@endcomponent
Salutations,<br>
{{ config('app.name') }}
@endcomponent
