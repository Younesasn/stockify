# Welcome to Stockify 📦

![image](https://img.shields.io/badge/Symfony-000000?style=for-the-badge&logo=Symfony&logoColor=white) ![image](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

## Introduction 🎬

Dans l'optique d'aborder le Framework Symfony, j'ai créé un projet du type `Google Drive`, dans lequel il est possible de stocker toute sortes de `fichers`. Le but était de me confronter à la façon de gérer l'upload de fichiers par utilisateur avec Symfony.

## Configuration ⚙️

Tout d'abord, [configurons notre environnement](INSTALLATION.md).

## Dashboard 🗂️

J'ai simplement générer un formulaire d'upload de fichier, avec l'affichage de fichiers par catégorie (Photos, Videos, Audios...). J'ai aussi implémenter une Progress Bar pour indiquer à l'utilisateur ou en est son stockage en fonction de son abonnement.

## EasyAdmin 👨🏾‍💼

J'ai intégré Easyadmin dans mon projet pour avoir un meilleur visuel sur l'ensemble de ma base de donnée.

### Suppression des fichiers 🗑️

Avec EasyAdmin, l'upload de fichier était assez simple à mettre en place. Pour ce qui est de la suppression, c'est une tout autre histoire...

La problématique était que quand je supprimais un `Upload` dans EasyAdmin, le fichier correspondant dans `/uploads/` ne se supprimait pas. Je trouve ça assez fourbe car une fois l'Upload supprimé, aucun message d'erreur ni d'avertissement nous indiquant quoi que ce soit tout à l'air de fonctionner.

Pour contrer ce problème, j'ai mis en place un Subscriber [`DeleteUploadSubscriber`](<src/EventSubscriber/DeleteUploadSubscriber.php>) qui s'assure qu'avant le `remove` du `Manager`, il me supprime le fichier correspondant dans `/uploads/`. (L'idéal aurait été de le faire juste après le `remove`).

## Events ⚠️

J'ai créé un [`SubscriptionRegisteredEvent`](<src/Event/SubscriptionRegisteredEvent.php>) au moment de l'inscription d'un `User`. J'ai ensuite souscris à cet `Event` pour envoyer un mail de remerciement à l'utilisateur (que j'ai d'ailleurs transformer en [`Service`](<src/Mail/SubscriptionService.php>)) et envoyer une notification [`Discord`](<src/EventSubscriber/SubscriptionRegisteredSubscriber.php>)

J'ai aussi créé un [`DeleteUserSubscriber`](<src/EventSubscriber/DeleteUserSubscriber.php>) pour supprimer tout les `Upload` du `User` correspondant dans la base de donnée puis supprimer son dossier dans `/uploads/`.

Le chiffrage de mot de passe aussi est un [`Subscriber`](<src/EventSubscriber/HashUserPasswordSubscriber.php>) que je met en place juste avant le `persist` du `Manager`.

Je n'es créé qu'un seul [`EventListener`](<src/EventListener/AddMyCorpHeaderListener.php>) qui envoie dans les Response Headers le nom du Développeur du projet.

## API Token 🔑

J'ai aussi exposé mon projet sous forme d'API si un utilisateur veut accéder à ses informations depuis son application ou autre.

Pour cela je fais générer pour chaque `User` un `Token` lors de son inscription. Il y a accès depuis son espace `Profil` dans la barre de navigation.

Ensuite il suffit juste d'indiquer l'Endpoint `/api/dashboard` en méthode `GET` puis d'insérer dans les `Headers` son `X-API-TOKEN`.

## Reset Password 🔄

J'ai généré un formulaire de réinitialisation de mot de passe pour les utilisateurs.

En envoyant son email dans le formulaire, un mail de 

J'avais une légère problématique pour la modification d'informations dans `Profil`, la modification n'était pas possible tant que le mot de passe n'était pas réindiquer, et ce n'était pas le but. J'ai préféré désactiver cette possibilité pour ne pas créer de problème de mot de passe.

## Conclusion 🔚

Après la réalisation d'un [projet](<https://github.com/Younesasn/power>) `PHP` sans framework, aborder Symfony est un pur régal 👨🏾‍🍳.

Plus sérieusement je n'imaginais pas à quel point ce framework pouvait nous ménager dans la réalisation d'un projet. Je suis assez satisfait dans ce que j'ai fourni je le pensais compliqué au départ, mais en le commençant petit à petit je me suis rendu compte que c'était largement faisable. J'imagine plusieurs fonctionnalités à l'avenir comme par exemple pouvoir uploadé plusieurs fichiers d'un coup, pouvoir créer une arborescence ou encore un système de partage de fichier...

En tout cas ce projet m'a permis de découvrir le Framework Symfony et de m'ouvrir à de nouvelles perspectives dans le développement Web.