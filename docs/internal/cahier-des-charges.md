# Exercice Technique — Développeur Laravel Mid-Level

**Bolli Rental** — Abidjan, Côte d'Ivoire
Référence poste : `BOLLI-DEV-2026-01`

---

## 1. Contexte

Bolli Rental est une startup de location de véhicules (berlines, SUV, vans) basée à Abidjan, avec disponibilité 24h/7j, transferts aéroport et livraison flexible.

Notre équipe service client reçoit chaque jour de nombreux appels : demandes de réservation, questions sur une location en cours, réclamations, problèmes de paiement mobile money. Aujourd'hui, ce suivi se fait sans outil dédié — les agents perdent du temps, l'information n'est pas centralisée, et le management n'a aucune visibilité sur le volume ou la qualité des appels traités.

**Votre mission :** construire un mini-outil interne de suivi des appels du service client, dans l'esprit d'un [Gong](https://www.gong.io) simplifié — un endroit où un agent peut enregistrer un appel, le qualifier, le suivre, et où le management peut voir un tableau de bord d'ensemble.

Cet exercice n'est pas un test de rapidité : c'est l'occasion de nous montrer comment vous structurez une application Laravel, priorisez sous contrainte de temps, et travaillez avec des outils d'IA — un point central du poste.

---

## 2. Ce que vous devez livrer (MVP obligatoire)

### a) Authentification

- Connexion simple pour les agents du service client (Laravel Breeze, Jetstream ou équivalent — libre à vous).

### b) Clients & réservations (données simplifiées)

- Un modèle `Client` et un modèle `Reservation` (véhicule, dates, statut). Pas besoin d'un module de réservation complet : quelques données de départ via **seeders** suffisent (10–20 clients, 20–30 réservations fictives).

### c) Suivi des appels (le cœur de l'exercice)

Pour chaque appel, un agent doit pouvoir enregistrer :

- le client concerné (recherche/sélection) ;
- le sens de l'appel (entrant / sortant) ;
- le motif (réservation, réclamation, support technique, paiement, autre) ;
- la date/heure et la durée ;
- l'agent qui a traité l'appel ;
- le statut (résolu, en attente, escaladé) ;
- des notes libres et des étiquettes (ex : `urgent`, `paiement`, `annulation`).

Vous devez pouvoir : lister les appels avec filtres (agent, statut, motif, période), consulter le détail d'un appel, et rattacher un appel à une réservation existante.

### d) Tableau de bord

Une vue de synthèse façon "analytics d'appels" :

- volume d'appels par jour/semaine ;
- répartition par motif et par statut ;
- durée moyenne des appels ;
- classement des agents par volume traité ;
- au moins un graphique (Chart.js ou équivalent).

---

## 3. Bonus (facultatifs, mais valorisés)

Ne faites les bonus que si le cœur ci-dessus est propre et fonctionnel. Un MVP solide vaut mieux qu'une liste de bonus bâclés.

- **Volet IA** (en écho direct au poste) : sur un appel, permettre d'ajouter un texte de transcript (ou un fichier audio si vous êtes à l'aise) et de générer automatiquement un résumé et/ou un sentiment via une API IA (OpenAI, Anthropic, ou autre). Idéalement en job asynchrone (queue).
- Notification (email ou notification Laravel) quand un appel est marqué `urgent`.
- Une API REST exposant les appels (préparant une future app mobile).
- Tests automatisés (PHPUnit ou Pest) sur les fonctionnalités clés.

---

## 4. Contraintes techniques

- **Laravel 10+**, PHP moderne, Eloquent ORM.
- Front-end libre : Blade, Vue.js ou Livewire.
- Base de données : MySQL, PostgreSQL ou SQLite (SQLite accepté si cela simplifie le déploiement gratuit).
- Code versionné avec des commits Git clairs et progressifs (pas un seul commit final).

---

## 5. Déploiement et rendu — obligatoire

1. **Code source** : dans un dépôt **GitHub public**, avec un `README.md` qui explique :
    - comment installer et lancer le projet en local ;
    - vos choix techniques et ce qui est fait / pas fait / simplifié, et pourquoi ;
    - **comment vous avez utilisé des outils d'IA** pendant l'exercice (Cursor, Claude Code, Copilot, etc.) — quelques lignes suffisent : ce que vous avez laissé générer, ce que vous avez corrigé ou repris à la main.
2. **Application en ligne** : déployée sur **Laravel Cloud (offre gratuite)**, accessible via une URL publique, avec un compte de démonstration (identifiants fournis dans le README) pour que nous puissions la tester.
3. **Envoi** : le lien du dépôt GitHub et le lien de l'application en ligne, envoyés avant votre entretien à `recrutement@bollirental.africa`, objet `[BOLLI-DEV-2026-01] – Exercice – Prénom NOM`.

---

## 6. Ce que nous évaluons

- Qualité et lisibilité du code, architecture Laravel (structure des modèles, contrôleurs, migrations).
- Pertinence des choix techniques compte tenu du temps disponible (savoir prioriser).
- UX simple et fonctionnelle — pas besoin d'un design abouti.
- Usage pertinent et documenté des outils IA (compétence indispensable pour ce poste).
- Clarté du README et facilité à faire tourner le projet.
- Respect du périmètre : un MVP propre et déployé vaut mieux qu'une V2 ambitieuse mais cassée.

---

## 7. Temps indicatif

Cet exercice est calibré pour un week-end (environ 6 à 10 heures au total). Il n'est pas nécessaire de tout implémenter en profondeur — nous cherchons à voir comment vous structurez et priorisez, pas un produit fini.

Bon courage, et n'hésitez pas à nous écrire si un point du brief n'est pas clair.

**Bolli Rental** — Abidjan, Côte d'Ivoire — bollirental.africa
