# VERYUMMY

## Installation

- `composer install`: Installer les librairies backend
- `npm install`: Installer les librairies frontend
- `php artisan migrate`: Créer la BDD et installer la structure de la BDD

## Lancement

- Lancer le serveur de la BDD
- `php artisan serve`: Lancer le serveur PHP
- `npm run dev`: Lancer la compoilation sous Vite JS
- Adresse dev local: http://127.0.0.1:8000/

## Commandes principales annexes

### Laravel Pint (codestyle)
- `./vendor/bin/pint`: Correction du codestyle (indentations, espaces)
- './vendor/bin/pint path/from/root.php': Correction d'un seul fichier

### Laravel PEST (test unitaire)
- `./vendor/bin/pest`: Lancement des tests PEST
- `./vendor/bin/pest --filter "name of test"`: Lancement d'un seul test
- `./vendor/bin/pest --coverage --min=90`: Test de couverture des tests avec un % minimum 

### Laravel Larastan (analyse statique)
- `./vendor/bin/phpstan analyse`: Analyse totale du projet selon le niveau défini das phpstan.neon
- `./vendor/bin/phpstan analyse --generate-baseline`: Analyse + rapport généré dans un fichier à la racine
- `./vendor/bin/phpstan analyse path/to/yourfile.php`: Analyse d'un seul fichier

