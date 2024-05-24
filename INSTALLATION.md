# Configuration du projet ⚙️

Installer le projet avec `Composer` :
```bash
composer install
```

## Doctrine 🪄

Créer un fichier `.env.local` à la racine du projet & configurer votre `base de donnée` :
```ini
DATABASE_URL="mysql://username:password@127.0.0.1:port/db_name?serverVersion=8.0.32&charset=utf8mb4"
```

Lancez les commandes :
```bash
php bin/console doctrine:database:create
```
```bash
php bin/console doctrine:migrations:migrate
```

Voici un [schéma représentatif](<assets/bdd.png>) de la base de donnée du projet.

## Mailtrap 📨

Installer un server local SMTP pour pouvoir recevoir les mails envoyés par Symfony.

### Pour Windows 📠

> Assurez-vous d'avoir installé [Docker](<https://docs.docker.com/desktop/install/windows-install/>) sur votre machine.

Lancez cette commande sur votre terminal :
```bash
docker run -d --name=mailtrap -p 8025:80 -p 1025:25 eaudeweb/mailtrap
```

Lancez le docker avec :
```bash
docker start mailtrap
```

Rendez-vous sur l'interface utilisateur `127.0.0.1:8025` pour pouvoir se connecter avec les informations suivantes :
- Identifiant : mailtrap 
- Mot de passe : mailtrap

Ajoutez dans le fichier `.env.local` la configuration du `Mailer` :
```ini
MAILER_DSN=smtp://127.0.0.1:1025
```

### Pour Mac 💻

Mailtrap avec Docker n'est pas disponible pour les Mac ayant les puces récentes (M1, M2, M3...). Mailhog est une solution !

Installer `Mailhog` avec `Brew` : 
```bash 
brew update && brew install mailhog
```

Le serveur SMTP démarre automatiquement sur le port `1025` et pour le serveur HTTP sur le port `8025`.

Lancer le server :
```bash 
brew services start mailhog
```

Rendez-vous sur `127.0.0.1:8025` pour voir l'interface utilisateur de Mailhog.

Ajoutez dans le fichier `.env.local` la configuration du `Mailer` :
```ini
MAILER_DSN=smtp://127.0.0.1:1025
```

## Notification Discord (Facultatif) 📳

Commencez par [créer un webhook](<https://serveur-prive.net/actualites/comment-creer-un-webhook-discord>) sur un serveur `Discord`.

Insérez dans le fichier `.env.local` le lien en ajoutant son `Token` & son `Webhook_ID` :
```ini
DISCORD_DSN=discord://TOKEN@default?webhook_id=ID
```

## Fixtures 🚧

Enfin, chargez les fixtures dans la base de donnée :
```bash
php bin/console doctrine:fixtures:load
```

Enjoy ! 🍾

Retournez au [README](<README.md>)