# API REST - Test Technique TOD

## Pré-requis

- PHP version 8.2 ou supérieure
- OpenSSL pour la gestion des clés permettant de générer le JWT

## Récupération du projet

Pour récupérer le projet en local, exécutez la commande suivante :

```
git clone https://github.com/devtrope/api_rest.git
```

## Installation du projet

Une fois le projet récupéré, installez les différentes dépendances du projet via la commande :

```
composer install
```

Il est possible que la console vous indique une erreur, la librairie gérant les JWT requiert l'extension Sodium de PHP. Vous pouvez l'installer ou ajouter le flag `--ignore-platform-req=ext-sodium` à la commande précédente pour l'ignorer temporairement.  

Dupliquez le fichier `.env` et renommez le en `.env.local`.

Renseignez vos informations de base de données dans le paramètre `DATABASE_URL` ainsi que les informations de votre SMTP dans le paramètre `MAILER_DSN`.

## Génération des clés pour le JWT

Afin de générer les clés publiques et privées, lancez tout d'abord la commande suivante pour créer le dossier qui contiendra les clés : 

```
mkdir -p config/jwt
```

Générez ensuite votre clé privée via la commande suivante :

```
openssl genrsa -out config/jwt/private.pem -aes256 4096
```

Il vous sera demandé de choisir une *pass phrase* puis de la répéter pour vérification. Cette *pass phrase* devra être ajouté dans votre `env.local` via le paramètre `JWT_PASSPHRASE`.

Pour finir, lancez la commande ci-dessous pour créer la clé publique. Une nouvelle fois votre *pass phrase* vous sera demandée.

```
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Création de la base de données

Afin de créer la base de données et les tables liées, lancez la commande :

```
php bin/console doctrine:migrations:migrate
```

Validez à la question qui vous sera posée, et votre base de données devrait alors être créée.

## Remplir la base de données

Afin d'avoir quelques données exploitables, un fichier `AppFixtures.php` a été mis en place. Pour pouvoir recevoir un mail à votre adresse mail lors de la validation du panier, rajoutez simplement votre adresse mail à cette ligne :

```
$user->setEmail("votre_adresse_mail");
```

Puis lancez la commande :

```
php bin/console doctrine:fixtures:load
```

Répondez *yes* à la question posée, et votre base de données devrait contenir des données pour les utilisateurs et les produits.

## Lancement du serveur

Pour lancer le serveur, lancez la commande : 

```
symfony server:start
```

Il devrait alors être accessible à l'adresse `127.0.0.1:8000`.

## Actions

Pour pouvoir tester l'API, rendez-vous sur Postman. Les 3 actions sont appelées via la méthode `POST`.

### Login

Dans un premier temps, récupérez votre login via l'URL `https://127.0.0.1:8000/api/login`. La méthode attend 2 paramètres, `email` et `password` (qui sera *password* pour l'utilisateur crée via la fixture).
Vous devriez recevoir un token si les informations renseignées sont correctes.

Pour les 2 autres actions, ce token doit être passé dans les autorisations en choisissant le type `Bearer Token` et en le collant dans le champ `Token`.

## Panier

Pour ajouter un produit au panier, renseignez l'URL `https://127.0.0.1:8000/api/cart`. Cette méthode attend 2 paramètres, `product_id` et `quantity`.

## Commande

Pour valider la commande, renseignez l'URL `https://127.0.0.1:8000/api/order`. Cette méthode attend 1 seul paramètre, `cart_id`.