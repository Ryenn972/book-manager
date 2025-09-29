# README

## Installation

git clone https://github.com/Ryenn972/book-manager

cd book_manager_ryenn

composer install

### Créer la BDD

CREATE DATABASE book_manager_ryenn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

### Connecter la BDD

Modifier DATABASE_URL dans le fichier .env

### Lancer les migrations

php bin/console doctrine:migrations:migrate

### Charger les fixtures

php bin/console doctrine:fixtures:load

### Lancer le serveur

symfony serve

### Logins

- Admin : admin@demo.com / adminpass

- Utilisateur : user@demo.com / userpass
