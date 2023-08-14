# VERYUMMY

## Installation

- `composer install`: Installer les librairies backend
- `npm install`: Installer les librairies frontend
- `php artisan migrate`: Créer la BDD et installer la structure de la BDD

## Lancement

- Lancer le serveur de la BDD
- `php artisan serve`: Lancer le serveur PHP
- Adresse dev local: http://127.0.0.1:8000/

## Commandes principales annexes

### Laravel Pint
- `./vendor/bin/pint`: Correction du codestyle (indentations, espaces)
- './vendor/bin/pint path/from/root.php': Correction d'un seul fichier

### Laravel PEST
- `./vendor/bin/pest`: Lancement des tests PEST
- `./vendor/bin/pest --filter "name of test"`: Lancement d'un seul test
- `./vendor/bin/pest --coverage --min=90`: Test de couverture des tests avec un % minimum 
