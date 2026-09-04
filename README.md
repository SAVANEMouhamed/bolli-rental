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
| Déploiement Laravel Cloud                   | ⛔ à faire — voir « Application en ligne »       |

Bonus du cahier des charges :

| Bonus                           | État                                                |
| ------------------------------- | --------------------------------------------------- |
| Tests automatisés               | ✅ 109 tests Pest                                   |
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

- Autorisation par `CallPolicy` : visibilité partagée sur le plateau — c'est le
  problème que l'outil résout — mais correction et suppression réservées à l'agent
  qui a enregistré l'appel. Vérifié côté serveur ; masquer un bouton dans Vue n'est
  jamais une protection.
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

- Déploiement Laravel Cloud
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

Pas encore déployée. L'URL Laravel Cloud sera ajoutée ici au moment du
déploiement ; le compte de démonstration ci-dessus y donnera accès.

### Procédure de déploiement

Laravel Cloud, offre gratuite, sur la branche `main`.

1. Connecter le dépôt GitHub, puis provisionner une base **Serverless Postgres** :
   les variables `DB_*` sont injectées par la plateforme.
2. Commande de build : `composer install --no-dev --optimize-autoloader`, puis
   `npm ci && npm run build`.
3. Commande de déploiement : `php artisan migrate --force` puis
   `php artisan db:seed --force`. Les seeders sont rejouables — un redéploiement
   ne duplique pas le jeu de démonstration et remet le mot de passe du compte de
   recette à la valeur publiée ci-dessus.
4. Variables d'environnement à poser sur la plateforme :

| Variable                | Valeur                | Pourquoi                                                    |
| ----------------------- | --------------------- | ----------------------------------------------------------- |
| `APP_ENV`               | `production`          | active les gardes de production (voir `AppServiceProvider`) |
| `APP_DEBUG`             | `false`               | aucune trace d'erreur exposée                               |
| `APP_URL`               | l'URL fournie         | liens absolus corrects, notamment dans les e-mails          |
| `SESSION_SECURE_COOKIE` | `true`                | cookie de session limité à HTTPS                            |
| `MAIL_*`                | les identifiants SMTP | sans quoi l'invitation d'un agent échoue                    |

Aucun de ces secrets ne vit dans le dépôt : `.env` est ignoré par Git et
`.env.example` ne porte que des valeurs neutres.

Les proxys de Laravel Cloud sont reconnus nativement par le framework, il n'y a
rien à configurer pour le HTTPS derrière le répartiteur.

L'instance et la base **hibernent** après une période d'inactivité sur l'offre
gratuite : la première requête peut prendre quelques secondes.

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
- Une `JsonResource` seule s'enveloppe dans une clé `data`. Les pages de détail
  attendaient l'objet lui-même : elles auraient reçu `call.data`. Corrigé par
  `resolve()`.
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
