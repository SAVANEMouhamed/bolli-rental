# Bolli Rental — Suivi des appels du service client

Outil interne pour l'équipe service client de Bolli Rental (Abidjan). Un agent y
enregistre chaque appel reçu ou passé, le qualifie (client, motif, statut, durée,
étiquettes) et le rattache si besoin à une réservation existante. Le management y
suit le volume et la qualité des appels traités.

Exercice technique `BOLLI-DEV-2026-01`.

---

## État d'avancement

> **Phase 1 — fondations. Les fonctionnalités métier ne sont pas encore
> développées.** Ce README décrit ce qui existe réellement dans le dépôt, rien de
> plus.

| Livrable du cahier des charges              | État                                           |
| ------------------------------------------- | ---------------------------------------------- |
| Authentification des agents                 | ✅ fait                                        |
| Modèles `Client` et `Reservation` + seeders | ✅ fait                                        |
| Schéma complet du suivi des appels          | ✅ modèles, migrations, relations, factories   |
| Enregistrement / liste / détail d'un appel  | ⛔ pas encore — phase 2                        |
| Filtres (agent, statut, motif, période)     | ⛔ pas encore — phase 2                        |
| Tableau de bord et graphique                | ⛔ pas encore — phase 2 (la page existe, vide) |
| Déploiement Laravel Cloud                   | ⛔ pas encore                                  |

Les bonus (IA, notifications, API REST) ne sont pas commencés, conformément à la
consigne « un MVP solide vaut mieux qu'une liste de bonus bâclés ».

---

## Stack

| Couche           | Choix                              | Version   |
| ---------------- | ---------------------------------- | --------- |
| Framework        | Laravel                            | 13.30     |
| Langage          | PHP                                | 8.4       |
| Liaison front    | Inertia                            | 3.0       |
| Front            | Vue 3 Composition API + TypeScript | 3.5 / 5.2 |
| CSS              | Tailwind                           | 4.1       |
| Authentification | Laravel Fortify (starter kit Vue)  | 1.37      |
| Base de données  | PostgreSQL                         | 17        |
| Tests            | Pest                               | 5.1       |
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
npm run check                      # lint + format du front (vite-plus)
npm run check:fix                  # idem, avec correction automatique
npm run types:check                # vérification TypeScript
npm run build                      # build de production
composer run ci:check              # tout l'ensemble ci-dessus
```

---

## Structure

```
app/Enums/           CallDirection, CallReason, CallStatus, ReservationStatus
app/Models/          User, Client, Reservation, Call, Tag
app/Providers/       défauts applicatifs (mot de passe, N+1), configuration Fortify
database/migrations/ schéma PostgreSQL
database/factories/  jeux de données de test
database/seeders/    étiquettes de référence, agents, données de démonstration
lang/fr/             messages de validation et d'authentification en français
resources/js/pages/  écrans Vue
resources/js/layouts/ mises en page (application, authentification, paramètres)
routes/              web.php, settings.php
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

**Inscription publique désactivée.** C'est un outil interne : les agents sont créés
par seeder, pas par formulaire ouvert. La vérification d'e-mail a été retirée dans
la foulée, faute de parcours d'inscription à vérifier.

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

**Pas de Repository, DTO, Action ni architecture hexagonale.** Conventions Laravel
par défaut. Un pattern ne sera introduit que face à un problème constaté.

---

## Fait / pas fait / simplifié

### Fait

- Projet Laravel 13 + Inertia + Vue 3 + TypeScript + Tailwind 4 opérationnel
- Authentification agents : connexion, mot de passe oublié, double authentification, clés d'accès
- Politique de mot de passe durcie (12 caractères, casse mixte, chiffre, symbole, contrôle des fuites connues)
- Schéma PostgreSQL complet : `clients`, `reservations`, `tags`, `calls`, `call_tag`, avec clés étrangères, contraintes d'intégrité et index justifiés
- Modèles Eloquent, enums, relations typées, factories et seeders de démonstration
- Interface entièrement en français, y compris les messages de validation Laravel
- Suite de tests verte, formatage et vérification de types passants

### Pas fait

- Enregistrement, liste, détail et modification d'un appel
- Filtres et pagination de la liste des appels
- Tableau de bord et graphiques
- Déploiement Laravel Cloud
- Tous les bonus (IA, notifications, API REST)

### Simplifié

- Les tests tournent sur SQLite en mémoire, l'application sur PostgreSQL. Les deux
  contraintes `CHECK` ne sont donc pas couvertes par la suite de tests ; elles sont
  vérifiées manuellement sur PostgreSQL.
- Pas de rôles ni de permissions : tout agent authentifié aura accès aux mêmes
  écrans. Le sujet ne distingue pas agent et management en termes d'accès.
- `Client` se limite à prénom, nom, téléphone et e-mail. Le sujet demande « les
  informations nécessaires », pas une fiche client complète.
- Les identifiants du compte de démonstration figurent en clair dans le seeder :
  le sujet exige un compte de recette documenté.

---

## Application en ligne

Pas encore déployée. L'URL Laravel Cloud et les identifiants seront ajoutés ici au
moment du déploiement.

---

## Usage des outils d'IA

Le projet est développé avec **Claude Code** (Claude Opus), en binôme.

**Ce qui a été généré par l'IA :** l'ossature du projet (commandes du starter kit
officiel), les migrations, modèles, enums, factories et seeders à partir d'une
spécification écrite du schéma, la traduction française de l'interface et des
messages de validation, et la rédaction de ce README.

**Ce qui a été cadré et corrigé à la main :** le périmètre, d'abord. Le dépôt porte
un ensemble de règles projet (`.claude/`) qui verrouillent le cahier des charges,
interdisent les bonus avant validation du MVP et imposent un arrêt à chaque fin de
phase. Plusieurs propositions de l'assistant ont été refusées à ce titre — une
base PostgreSQL en conteneur alors qu'une instance existait déjà, une
initialisation de dépôt non demandée.

**Corrections techniques reprises à la main pendant la phase 1 :**

- `Factory::state()` relie ses closures à la factory : un seeder qui appelait
  `$this->…` depuis une closure d'état échouait. Réécrit avec des captures explicites.
- La désactivation de la vérification d'e-mail supprimait les routes `verification.*`,
  ce qui cassait le build Wayfinder à travers un import Vue resté en place.
- `uncompromised()` appelle l'API Have I Been Pwned : la règle est désactivée sous
  test pour ne pas rendre la suite dépendante du réseau.
- Le formateur du starter kit réécrivait toute la documentation Markdown du dépôt ;
  `.claude/` et `docs/` ont été exclus de son périmètre.
- La page d'accueil du starter kit (436 lignes de contenu promotionnel Laravel) et
  les liens de pied de barre latérale vers la documentation Laravel ont été
  remplacés : ils n'ont pas leur place dans un outil livré à un client.

**Vérification systématique :** chaque étape est contrôlée par exécution réelle —
migrations rejouées, données de démonstration inspectées en SQL, contraintes
`CHECK` testées par une insertion invalide, connexion au compte de démonstration
effectuée en HTTP, suite de tests et build exécutés — plutôt que sur la seule
lecture du code produit.
