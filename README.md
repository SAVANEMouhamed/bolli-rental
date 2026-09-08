# Bolli Rental — Suivi des appels du service client

Outil interne pour l'équipe service client de Bolli Rental (Abidjan). Un agent y
enregistre chaque appel reçu ou passé, le qualifie (client, motif, statut, durée,
étiquettes) et le rattache si besoin à une réservation existante. Le management y
suit le volume et la qualité des appels traités.

Exercice technique `BOLLI-DEV-2026-01`.

---

## État d'avancement

Ce tableau décrit ce qui existe réellement dans le dépôt, rien de plus.

| Livrable du cahier des charges              | État                                             |
| ------------------------------------------- | ------------------------------------------------ |
| Authentification des agents                 | ✅ fait                                          |
| Modèles `Client` et `Reservation` + seeders | ✅ 16 clients, 26 réservations                   |
| Enregistrement / liste / détail d'un appel  | ✅ fait, avec modification et suppression        |
| Filtres agent, statut, motif, période       | ✅ fait, plus sens, étiquette et recherche texte |
| Rattachement d'un appel à une réservation   | ✅ fait, avec vérification serveur du client     |
| Tableau de bord et graphique                | ✅ fait, deux types de graphiques                |
| Déploiement                                 | ⚙️ chaîne prête, mise en ligne à faire           |

Bonus du cahier des charges :

| Bonus                           | État                                                |
| ------------------------------- | --------------------------------------------------- |
| Tests automatisés               | ✅ 118 tests Pest                                   |
| API REST exposant les appels    | ✅ `/api/v1`, en lecture, documentée en OpenAPI 3.1 |
| Volet IA (résumé, sentiment)    | ⛔ non commencé                                     |
| Notification sur appel `urgent` | ⛔ non commencé                                     |

Hors cahier des charges, ajouté à la demande : **envoi des accès agent par
e-mail** — un agent en invite un autre, qui reçoit un lien pour définir son mot
de passe.

---

## Stack

| Couche           | Choix                              | Version   |
| ---------------- | ---------------------------------- | --------- |
| Framework        | Laravel                            | 13.30     |
| Langage          | PHP                                | 8.4       |
| Liaison front    | Inertia                            | 3.3       |
| Front            | Vue 3 Composition API + TypeScript | 3.5 / 5.2 |
| CSS              | Tailwind                           | 4.1       |
| Graphiques       | Chart.js                           | 4.5       |
| Authentification | Laravel Fortify (starter kit Vue)  | 1.39      |
| Base de données  | PostgreSQL                         | 17        |
| Tests            | Pest                               | 5.1       |
| Analyse statique | Larastan / PHPStan niveau 7        | 3.11      |
| Build / qualité  | Vite + vite-plus, Laravel Pint     | 8.0 / 0.3 |

---

## Prérequis

- PHP **8.3 minimum** (8.4 utilisé ici) avec les extensions `pdo_pgsql`, `mbstring`, `intl`, `zip`, `bcmath`
- Composer 2
- Node **20+** et npm
- PostgreSQL 14+ (ou tout serveur PostgreSQL accessible)

Sur macOS, [Laravel Herd](https://herd.laravel.com) fournit PHP, Composer et la
CLI Laravel en une installation.

---

## Installation locale

```bash
git clone <url-du-depot> bolli-rental
cd bolli-rental

cp .env.example .env
# renseigner la section base de données du .env (voir ci-dessous)

composer install
npm install

php artisan key:generate
php artisan migrate --seed
npm run build
```

### Configuration `.env`

Créez au préalable une base vide, puis renseignez :

| Variable        | Rôle                                                |
| --------------- | --------------------------------------------------- |
| `APP_NAME`      | Nom affiché dans l'interface et les onglets         |
| `APP_URL`       | URL de base de l'application                        |
| `APP_LOCALE`    | `fr` — l'interface et les messages sont en français |
| `DB_CONNECTION` | `pgsql`                                             |
| `DB_HOST`       | Hôte PostgreSQL                                     |
| `DB_PORT`       | Port PostgreSQL (5432 par défaut)                   |
| `DB_DATABASE`   | Nom de la base                                      |
| `DB_USERNAME`   | Utilisateur PostgreSQL                              |
| `DB_PASSWORD`   | Mot de passe PostgreSQL                             |

Pour l'envoi réel des accès agent et des liens de réinitialisation :

| Variable            | Rôle                                                                             |
| ------------------- | -------------------------------------------------------------------------------- |
| `MAIL_MAILER`       | `smtp` pour envoyer, `log` pour écrire l'e-mail dans les journaux                |
| `MAIL_SCHEME`       | `smtp` (STARTTLS, port 587) ou `smtps` (TLS implicite, port 465)                 |
| `MAIL_HOST`         | Serveur SMTP                                                                     |
| `MAIL_PORT`         | Port SMTP                                                                        |
| `MAIL_USERNAME`     | Identifiant SMTP                                                                 |
| `MAIL_PASSWORD`     | Mot de passe SMTP — mot de passe d'application, jamais le mot de passe du compte |
| `MAIL_FROM_ADDRESS` | Expéditeur des e-mails de l'application                                          |

`MAIL_MAILER=log` suffit pour développer : l'e-mail complet, lien compris, est
écrit dans `storage/logs/laravel.log`.

`.env` n'est jamais versionné. `.env.example` ne contient aucune valeur réelle.

### Lancement

```bash
composer run dev     # serveur PHP + worker de queue + Vite, en parallèle
```

L'application est alors disponible sur <http://localhost:8000>.

### Compte de démonstration

Créé par les seeders. L'inscription publique est désactivée : c'est le seul accès.

```
demo@bollirental.africa
Bolli@Demo2026!
```

---

## Commandes utiles

```bash
php artisan migrate:fresh --seed   # réinitialise la base et rejoue les données de démo
php artisan test                   # suite Pest
vendor/bin/pint                    # formatage PHP
vendor/bin/phpstan analyse         # analyse statique niveau 7
npm run check                      # lint + format du front (vite-plus)
npm run check:fix                  # idem, avec correction automatique
npm run types:check                # vérification TypeScript
npm run build                      # build de production
composer run ci:check              # tout l'ensemble ci-dessus
```

---

## Écrans

| Route           | Contenu                                                                      |
| --------------- | ---------------------------------------------------------------------------- |
| `/dashboard`    | Indicateurs, volume par jour ou semaine, répartitions, classement des agents |
| `/calls`        | Liste filtrable et paginée des appels                                        |
| `/calls/create` | Enregistrement d'un appel                                                    |
| `/calls/{id}`   | Détail, modification, suppression                                            |
| `/clients`      | Fiches clients avec volume d'échanges                                        |
| `/clients/{id}` | Réservations et historique d'appels d'un client                              |
| `/reservations` | Locations, filtrables par statut                                             |
| `/agents`       | Plateau et invitation d'un nouvel agent                                      |
| `/docs/api`     | Documentation Swagger de l'API                                               |

---

## API REST

Sept endpoints en lecture sous `/api/v1`, authentifiés par la session de
l'application :

```
GET /api/v1/calls                    filtres identiques à l'écran web, paginé
GET /api/v1/calls/{call}
GET /api/v1/clients
GET /api/v1/clients/{client}
GET /api/v1/reservations
GET /api/v1/reservations/{reservation}
GET /api/v1/statistics               agrégations du tableau de bord
```

L'enveloppe `data` n'apparaît que sur les listes paginées, où elle doit loger
`links` et `meta` à côté des lignes ; une ressource unique et les statistiques
sont renvoyées telles quelles. C'est la même règle que pour les props Inertia,
posée une fois dans `AppServiceProvider`.

La documentation OpenAPI 3.1 est servie sur **`/docs/api`** (Swagger UI, avec
« Try it out » fonctionnel depuis un navigateur connecté) et le document brut sur
`/api/documentation.json`.

---

## Structure

```
app/Enums/            CallDirection, CallReason, CallStatus, ReservationStatus
app/Models/           User, Client, Reservation, Call, Tag
app/Http/Controllers/ écrans web ; Api/V1/ pour l'API REST
app/Http/Requests/    validation des formulaires et des filtres
app/Http/Resources/   mise en forme partagée entre écrans web et API
app/Policies/         CallPolicy
app/Support/          CallStatistics (agrégations), OpenApiDocument
app/Notifications/    AgentInvitation
app/Providers/        défauts applicatifs (mot de passe, N+1), configuration Fortify
database/migrations/  schéma PostgreSQL
database/factories/   jeux de données de test
database/seeders/     étiquettes de référence, agents, données de démonstration
lang/fr/              messages de validation et d'authentification en français
resources/js/pages/   écrans Vue
resources/js/components/ composants partagés, dont charts/
resources/js/composables/ useFilters (filtres synchronisés avec l'URL)
routes/               web.php, settings.php, api.php
```

### Modèle de données

```
User (agent) 1─n Call
Client       1─n Reservation
Client       1─n Call
Reservation  1─n Call        (calls.reservation_id nullable)
Call         n─n Tag         (pivot call_tag)
```

Les données de démonstration couvrent 4 agents, 16 clients, 26 réservations et
320 appels répartis sur les 8 dernières semaines.

---

## Choix architecturaux

**Inertia plutôt qu'une SPA séparée.** Le cahier des charges impose Laravel comme
application principale. Inertia garde les routes, l'authentification, la
validation et l'autorisation côté Laravel, tout en permettant d'écrire l'interface
en Vue. Une SPA autonome aurait dédoublé le routage et la validation.

**Fortify via le starter kit Vue officiel, pas Breeze ni Jetstream.** Le sujet dit
« Breeze, Jetstream ou équivalent ». Breeze et Jetstream sont gelés depuis
Laravel 12 ; le starter kit Vue officiel, bâti sur Fortify, est leur remplaçant
supporté. Il apporte session sécurisée, hachage, CSRF, limitation de débit sur la
connexion, double authentification et clés d'accès.

**PostgreSQL.** Autorisé par le sujet et disponible sur Laravel Cloud. Il permet
des contraintes `CHECK` réelles (`ends_at > starts_at`, `duration_seconds >= 0`)
que SQLite ne sait pas ajouter après création de table.

**Table pivot pour les étiquettes plutôt qu'une colonne JSON.** Une colonne JSON
est plus rapide à écrire, mais le filtrage et le comptage par étiquette — exigés
par les filtres et le tableau de bord — deviennent coûteux et mal indexables. Le
pivot `call_tag` porte une clé primaire composite et un index inverse.

**Index justifiés un par un.** Chaque index de `calls` répond à une requête prévue
du projet (volume par période, filtre agent, filtre statut, filtre motif, appels
d'un client, appels d'une réservation) et porte cette requête en commentaire.
PostgreSQL n'indexe pas automatiquement les colonnes portantes de clés étrangères,
d'où les index explicites sur `client_id` et `reservation_id`.

**Les filtres vivent dans le modèle, pas dans le contrôleur.** Le scope
`Call::filtered()` porte les filtres de la liste. L'écran web et l'API REST
l'appellent tous les deux : ils ne peuvent pas diverger. Même raisonnement pour
`App\Support\CallStatistics`, partagé par le tableau de bord et l'endpoint
statistiques — c'est cette réutilisation constatée qui justifie la classe, pas
un goût pour les couches.

**L'état des filtres vit dans l'URL.** Une liste filtrée est rechargeable et
partageable entre agents, et les filtres restent testables côté serveur.

**`user_id` n'est jamais accepté du client HTTP.** L'agent d'un appel est celui de
la session. Sinon n'importe qui pourrait créditer un collègue et fausser le
classement du tableau de bord.

**Statistiques calculées par la base.** `COUNT`, `AVG`, `GROUP BY` et une jointure.
Aucune collection d'appels n'est chargée en mémoire pour être parcourue en PHP.
Le regroupement temporel utilise `DATE()` plutôt que `date_trunc()` : c'est la
seule fonction de date comprise à l'identique par PostgreSQL et par SQLite, sur
lequel tourne la suite de tests. Le cumul hebdomadaire porte ensuite sur les
compteurs journaliers — quelques dizaines de lignes déjà agrégées.

**Pas de Repository, DTO, Action ni architecture hexagonale.** Conventions Laravel
par défaut.

---

## Sécurité

- Autorisation par `CallPolicy` : historique partagé sur le plateau — c'est le
  problème que l'outil résout — donc tout agent peut reprendre un appel et faire
  évoluer son statut, mais seul son auteur peut le supprimer de l'historique
  commun. L'auteur n'est jamais réécrit lors d'une correction, sans quoi le
  classement du tableau de bord serait faussé. Vérifié côté serveur ; masquer un
  bouton dans Vue n'est jamais une protection.
- Filtres de liste validés avant d'atteindre le SQL : enums contraints par
  `Rule::enum`, identifiants par `Rule::exists`, granularité par liste blanche.
- Une réservation rattachée doit appartenir au client de l'appel — vérifié en base
  par la Form Request, pas seulement par la liste déroulante.
- Zone « Agents » protégée par une reconfirmation du mot de passe et limitée en
  débit : créer un accès est une action sensible.
- L'invitation ne transporte aucun mot de passe. Le compte est créé avec un secret
  aléatoire que personne ne connaît, et l'invité reçoit un lien à usage unique et
  expirant — celui du flux de réinitialisation de Fortify, plutôt qu'un mécanisme
  d'invitation parallèle à réauditer.
- Politique de mot de passe : 12 caractères, casse mixte, chiffre, symbole, et
  contrôle des fuites connues via Have I Been Pwned hors tests.

---

## Fait / pas fait / simplifié

### Fait

- MVP complet du cahier des charges : authentification, clients, réservations,
  suivi des appels avec filtres et rattachement, tableau de bord avec graphiques
- Invitation d'un agent par e-mail
- API REST en lecture avec documentation Swagger
- 106 tests Pest, PHPStan niveau 7 sans erreur, TypeScript sans erreur
- Interface entièrement en français, y compris les messages de validation Laravel

### Pas fait

- Mise en ligne effective (la chaîne de déploiement, elle, est prête et testée)
- Volet IA (résumé, sentiment, transcript)
- Notification sur appel marqué `urgent`
- Écriture via l'API

### Simplifié

- **Pas de rôles.** Tout agent authentifié voit les mêmes écrans et peut inviter un
  collègue. Le cahier des charges ne distingue pas agent et management en termes
  d'accès. Les points d'autorisation existent (`CallPolicy`, reconfirmation du mot
  de passe) et se resserreraient sans refonte.
- **API en lecture seule, authentifiée par session.** Ouvrir l'écriture à un client
  mobile suppose une authentification par jeton (Laravel Sanctum), qui ajoute une
  dépendance non validée. « Exposer les appels », le besoin annoncé au titre du
  bonus, est couvert.
- **Documentation OpenAPI construite en PHP** plutôt que par un générateur
  (`dedoc/scramble`), pour la même raison de dépendance. Les valeurs qui dérivent
  le plus vite — motifs, statuts, sens d'appel — sont lues sur les enums de
  l'application, et un test verrouille cette correspondance.
- **Les tests tournent sur SQLite en mémoire, l'application sur PostgreSQL.** Les
  deux contraintes `CHECK` ne sont donc pas couvertes par la suite ; les requêtes
  brutes ont été rejouées manuellement sur PostgreSQL.
- **`Client` se limite** à prénom, nom, téléphone et e-mail. Le sujet demande « les
  informations nécessaires », pas une fiche client complète.
- **Clients et réservations sont en consultation seule**, alimentés par seeders,
  comme le prévoit explicitement le §2b du cahier des charges.
- **Les identifiants du compte de démonstration figurent en clair** dans le seeder :
  le sujet exige un compte de recette documenté.

---

## Application en ligne

Pas encore déployée : l'URL publique sera ajoutée ici, et le compte de
démonstration ci-dessus y donnera accès.

### Pourquoi pas Laravel Cloud

Le §5.2 du cahier des charges demande un déploiement sur **Laravel Cloud (offre
gratuite)**. Le rendu s'en écarte volontairement : l'hébergement se fait sur un
**VPS administré avec 1Panel**, faute d'offre gratuite exploitable au moment du
rendu. L'exigence de fond — une URL publique testable avec un compte de
démonstration documenté — est tenue à l'identique. C'est le seul écart au sujet.

### Architecture du déploiement

**Un seul conteneur applicatif.** La base PostgreSQL est gérée par 1Panel, hors
composition, et c'est le proxy de 1Panel qui porte le domaine et TLS.

L'image repose sur **FrankenPHP** plutôt que sur nginx + PHP-FPM : PHP-FPM ne
parle que FastCGI et ne peut donc pas être seul derrière un proxy HTTP. FrankenPHP
embarque Caddy, sert les assets statiques et PHP dans le même processus, et tourne
ici en **mode classique** — pas en mode worker Octane : la sémantique de requête
reste exactement celle de PHP-FPM, sans état partagé entre requêtes.

| Fichier                        | Rôle                                            |
| ------------------------------ | ----------------------------------------------- |
| `Dockerfile`                   | Construction (Composer, Node) puis image finale |
| `docker/Caddyfile`             | Serveur HTTP, compression, cache des assets     |
| `docker/entrypoint.sh`         | Attente base, migrations, seeders, caches       |
| `docker-compose.yml`           | Le service applicatif                           |
| `.github/workflows/deploy.yml` | Tests, image, livraison                         |

L'étage de construction pèse ~780 Mo (Composer, Node, `node_modules`) ; l'image
finale ~350 Mo, dont 263 Mo de base FrankenPHP. Elle ne contient ni Node, ni
Composer, ni dépendances de développement, ni `.env`, et tourne sous `www-data`.

**Tout est automatique au démarrage** : attente de la base, migrations, jeu de
démonstration, puis caches de configuration, de routes, de vues et d'événements.
Les seeders sont idempotents ; un redéploiement ne duplique rien.

### Mise en place, une seule fois

Trois gestes manuels, et rien d'autre :

1. Créer la base PostgreSQL dans **1Panel**.
2. Créer le répertoire de déploiement sur le VPS, appartenant à l'utilisateur SSH,
   qui doit être membre du groupe `docker`.
3. Créer un site dans **1Panel**, en proxy inverse vers `127.0.0.1:8080`, avec son
   certificat.

Puis renseigner le dépôt GitHub. Le fichier d'environnement du serveur et la
composition sont **rendus et déposés par la chaîne à chaque livraison** : rien
n'est à écrire ni à maintenir sur le VPS.

**Secrets** (`Settings › Secrets and variables › Actions › Secrets`) :

| Secret           | Contenu                                     |
| ---------------- | ------------------------------------------- |
| `DEPLOY_HOST`    | adresse du VPS                              |
| `DEPLOY_USER`    | utilisateur SSH                             |
| `DEPLOY_PATH`    | répertoire de déploiement                   |
| `DEPLOY_SSH_KEY` | clé privée dédiée, sans phrase de passe     |
| `DEPLOY_PORT`    | port SSH — facultatif, `22` par défaut      |
| `APP_KEY`        | sortie de `php artisan key:generate --show` |
| `DB_PASSWORD`    | mot de passe de la base créée dans 1Panel   |
| `MAIL_PASSWORD`  | mot de passe SMTP — facultatif              |

**Variables** (même écran, onglet `Variables`) :

| Variable                                                          | Défaut                                  |
| ----------------------------------------------------------------- | --------------------------------------- |
| `APP_URL` — requis                                                | —                                       |
| `DB_HOST`                                                         | `host.docker.internal`                  |
| `DB_PORT` · `DB_DATABASE` · `DB_USERNAME`                         | `5432` · `bolli` · `bolli`              |
| `APP_PORT`                                                        | `8080`                                  |
| `MAIL_HOST` · `MAIL_PORT` · `MAIL_USERNAME` · `MAIL_FROM_ADDRESS` | vide ⇒ e-mails écrits dans les journaux |
| `SEED_ON_DEPLOY`                                                  | `true`                                  |

`APP_KEY` est un secret et non une valeur générée à chaque livraison : la
régénérer invaliderait toutes les sessions et tous les cookies chiffrés.

Le job échoue avec un message explicite si `APP_KEY`, `DB_PASSWORD` ou `APP_URL`
manquent, plutôt que de livrer une application qui ne démarrera pas.

### Ce que fait un push

`develop` et `main` livrent sur le **même** environnement : la dernière livraison
gagne, et un verrou de concurrence empêche deux déploiements de se croiser. Une
pull request ne déclenche que les contrôles.

1. **Contrôles** — formatage, lint, types, PHPStan niveau 7, 118 tests Pest, plus
   les migrations et les seeders rejoués sur un vrai PostgreSQL.
2. **Image** — publication sur GHCR en `latest` et en `sha-<commit>`, avec cache
   de couches dans le registre.
3. **Configuration** — rendu du fichier d'environnement à partir des secrets et
   des variables, en `0600`, valeurs échappées.
4. **Livraison** — dépôt de la composition et de la configuration, puis
   `docker compose up -d --wait`, qui attend la **santé** du conteneur : une
   migration en échec fait échouer la livraison au lieu de publier une
   application morte.

Revenir en arrière depuis le VPS, sans rien réécrire :

```bash
APP_IMAGE=ghcr.io/<compte>/bolli-rental:sha-<commit> docker compose up -d --wait
```

### Deux points assumés

**`fakerphp/faker` est une dépendance de production.** L'image est construite avec
`--no-dev`, et le jeu de démonstration exigé au §2b est produit par des factories
qui s'appuient sur Faker. Sans ce déplacement, le conteneur démarre puis échoue au
seeding. C'est le prix d'un déploiement qui est une démonstration : sur une vraie
production, les seeders et Faker disparaîtraient ensemble.

**`TRUSTED_PROXIES=*` est sûr ici, et seulement ici.** Le conteneur n'est publié
que sur la boucle locale : le proxy est le seul émetteur possible des en-têtes
`X-Forwarded-*`. Sans cette valeur, Laravel se croit en clair, fabrique des liens
`http` dans les e-mails d'invitation et rend `SESSION_SECURE_COOKIE` inopérant.
Deux tests couvrent les deux sens de la règle.

---

## Usage des outils d'IA

Le projet est développé avec **Claude Code** (Claude Opus), en binôme.

**Ce qui a été généré par l'IA :** l'ossature du projet, les migrations, modèles,
enums, factories et seeders à partir d'une spécification écrite du schéma ; les
contrôleurs, Form Requests, Policies et API Resources ; les écrans Vue et les
composants de graphique ; la suite de tests ; la traduction française de
l'interface ; et la rédaction de ce README.

**Ce qui a été cadré et corrigé à la main :** le périmètre, d'abord. Le dépôt porte
un ensemble de règles projet (`.claude/`) qui verrouillent le cahier des charges,
interdisent les bonus avant validation du MVP et imposent un arrêt à chaque fin de
phase. Plusieurs propositions de l'assistant ont été refusées à ce titre — une
base PostgreSQL en conteneur alors qu'une instance existait déjà, une
initialisation de dépôt non demandée.

**Défauts trouvés par vérification réelle, pas par relecture :**

- Le composant `Checkbox` de reka-ui rend un `<input>` sans attribut `type`, donc
  un champ texte toujours présent dans le `FormData` avec la valeur `on`.
  `$request->boolean('remember')` lisait `on` comme vrai : **tout agent restait
  connecté durablement, case décochée comprise.** Corrigé par un champ caché piloté
  par la case, puis vérifié en HTTP sur l'application lancée — cookie absent avec
  `remember=0`, présent avec `remember=1`.
- Une `JsonResource` passée en prop Inertia s'enveloppe dans une clé `data`. Le
  premier correctif — un `resolve()` sur les ressources uniques — n'a traité que
  les cas visibles. Les collections des filtres restaient enveloppées :
  `options.agents` arrivait en `{data: [...]}` là où la page appelle `.map()`, et
  **l'écran des appels, cœur de l'exercice, s'affichait entièrement blanc**. La
  requête répondant 200, ni les tests ni les journaux serveur ne le signalaient ;
  le défaut a été trouvé en ouvrant la page, puis localisé en comparant les props
  réellement envoyées aux types TypeScript qui les décrivent. Corrigé à la racine
  par `JsonResource::withoutWrapping()` — une règle unique plutôt que dix
  `resolve()` locaux qu'une resource ajoutée plus tard oublierait — et couvert par
  cinq tests qui figent la forme des props de chaque écran.
- La liste des appels ne chargeait la réservation et le client que partiellement,
  alors que les resources formatent toujours les dates et l'e-mail. La page tombait
  en 500 dès qu'un appel portait une réservation — **invisible pour la suite de
  tests, parce que la factory laisse `reservation_id` à null et qu'aucun test ne
  listait un appel réellement rattaché.** Trouvé en parcourant l'application réelle
  sur PostgreSQL. Corrigé, couvert par deux tests de régression, et surtout
  prévenu : `Model::preventAccessingMissingAttributes()` est activé hors production,
  ce qui a immédiatement révélé deux autres occurrences du même défaut.
- Une régénération manuelle de Wayfinder sans `--with-form` supprime les helpers
  `.form()` et casse une douzaine de composants du starter kit. Le piège est
  désormais consigné dans les règles du dépôt.
- `Factory::state()` relie ses closures à la factory : un seeder qui appelait
  `$this->…` depuis une closure d'état échouait. Réécrit avec des captures explicites.
- `uncompromised()` appelle l'API Have I Been Pwned : la règle est désactivée sous
  test pour ne pas rendre la suite dépendante du réseau.

**Vérification systématique :** chaque étape est contrôlée par exécution réelle
plutôt que sur la lecture du code produit — migrations rejouées, requêtes
d'agrégation exécutées sur PostgreSQL et non seulement sur le SQLite des tests,
application parcourue page par page en HTTP avec une session authentifiée, envoi
SMTP réellement effectué, suite de tests, analyse statique et build exécutés avant
chaque commit.
