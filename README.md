# README

## Installation

git clone https://github.com/Ryenn972/book-manager
cd book_manager_ryenn

composer install

### Créer la BDD

CREATE DATABASE book_manager_ryenn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

### Connecter la BDD

Modifier DATABASE_URL par => DATABASE_URL="mysql://root@127.0.0.1:3306/book_manager_ryenn?serverVersion=9.1.0&charset=utf8mb4"

### Lancer les migrations

php bin/console doctrine:migrations:migrate

### Charger les fixtures

php bin/console doctrine:fixtures:load

### Lancer le serveur

symfony serve

### Logins

- Admin : admin@demo.com / adminpass

- Utilisateur : user@demo.com / userpass
