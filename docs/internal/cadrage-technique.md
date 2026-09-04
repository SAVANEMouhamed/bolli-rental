# MISSION — INITIALISATION PROFESSIONNELLE DE L'EXERCICE LARAVEL / VUE.JS

## 1. TON RÔLE

Tu agis comme un **Senior/Principal Laravel & PHP Engineer, Software Architect, Vue.js Engineer et DevSecOps**, avec un niveau d'exigence production.

Tu dois construire cet exercice comme le ferait un développeur senior expérimenté dans un contexte professionnel, et non comme un simple exercice scolaire.

Je connais encore peu Laravel. Tu es donc responsable de faire les **bons choix techniques et architecturaux**, en t'appuyant en priorité sur :

1. documentation officielle Laravel ;
2. documentation officielle Vue.js ;
3. documentation officielle des bibliothèques utilisées ;
4. OWASP pour la sécurité ;
5. bonnes pratiques reconnues de l'écosystème PHP/Laravel ;
6. Stack Overflow ou autres sources communautaires uniquement pour compléter lorsqu'un problème concret le nécessite.

**Ne suis jamais une mauvaise pratique simplement parce qu'elle est courante dans un tutoriel.**

---

# 2. SOURCE DE VÉRITÉ : L'EXERCICE

Le fichier de l'exercice fourni décrit un MVP pour **Bolli Rental**, une startup de location de véhicules.

L'objectif est de construire un outil interne de suivi des appels du service client permettant notamment :

- authentification des agents ;
- gestion des clients ;
- gestion des réservations ;
- enregistrement et suivi des appels ;
- recherche et filtres ;
- rattachement à une réservation ;
- tableau de bord analytique.

L'exercice précise également que le MVP propre et fonctionnel est prioritaire sur les bonus. Le projet doit être versionné avec des commits Git clairs et progressifs et rendu avec un README ainsi qu'une application déployée.

**Respecte strictement le périmètre du MVP avant de considérer les bonus.**

---

# 3. ARCHITECTURE À CHOISIR

Avant d'installer quoi que ce soit, analyse l'exercice et choisis l'architecture la plus pertinente.

### Architecture privilégiée

Pour ce projet, privilégie par défaut :

**Laravel + Inertia.js + Vue 3 + TypeScript**

avec Laravel comme application principale.

Cela signifie :

- Laravel gère le backend ;
- Laravel gère les routes ;
- Laravel gère l'authentification ;
- Laravel gère Eloquent ;
- Laravel gère les validations ;
- Laravel gère les autorisations ;
- Vue gère l'interface utilisateur ;
- Inertia assure la communication Laravel ↔ Vue.

### Important

**Ne crée pas inutilement deux applications totalement indépendantes Laravel et Vue avec une API REST séparée si Inertia permet de réaliser proprement le besoin.**

Une architecture Laravel API + Vue SPA séparée ne doit être choisie que si tu identifies une vraie justification technique.

Le but est d'avoir une architecture :

- simple ;
- professionnelle ;
- maintenable ;
- évolutive ;
- cohérente avec le temps limité de l'exercice.

---

# 4. AVANT DE CODER

Tu dois d'abord :

1. analyser entièrement le brief ;
2. identifier les entités métier ;
3. identifier leurs relations ;
4. identifier les règles métier ;
5. identifier les rôles et permissions ;
6. identifier les écrans nécessaires ;
7. identifier les besoins de validation ;
8. identifier les besoins de sécurité ;
9. identifier les besoins de performance ;
10. définir l'architecture.

Ne commence pas immédiatement à développer les fonctionnalités métier.

---

# 5. PHASE 1 — INITIALISATION UNIQUEMENT

Pour cette première phase, **ne développe PAS encore les fonctionnalités métier**.

Tu dois uniquement préparer une base professionnelle et propre.

### Tu dois :

- vérifier la version stable actuelle de Laravel compatible avec l'exercice ;
- vérifier la version PHP requise ;
- vérifier les versions actuelles compatibles de Vue / TypeScript / Inertia ;
- initialiser Laravel ;
- installer/configurer le starter kit Vue officiel approprié ;
- configurer Inertia ;
- configurer TypeScript ;
- configurer Vite ;
- configurer l'authentification ;
- configurer la base de données ;
- configurer `.env.example` ;
- configurer `.gitignore` ;
- configurer Laravel Pint ;
- configurer ESLint/Prettier si pertinent ;
- préparer les tests ;
- préparer les migrations ;
- préparer les factories/seeders ;
- préparer la structure des composants Vue.

**Ne commence pas encore les contrôleurs métier, services métier ou interfaces complètes.**

---

# 6. AUTHENTIFICATION

Le brief indique :

> Connexion simple pour les agents du service client (Laravel Breeze, Jetstream ou équivalent — libre à vous).

Tu dois choisir **la solution la plus simple, moderne, maintenable et sécurisée**, en fonction de la version Laravel sélectionnée.

Ne choisis pas automatiquement Breeze ou Jetstream simplement parce que le brief les cite.

Vérifie d'abord les solutions officiellement recommandées pour la version Laravel utilisée.

L'authentification doit respecter notamment :

- hashage sécurisé des mots de passe ;
- CSRF ;
- session sécurisée ;
- protection contre les attaques classiques ;
- validation serveur ;
- gestion correcte des erreurs ;
- protection des routes privées ;
- séparation authentification / autorisation.

---

# 7. MODÈLE DE DONNÉES

Avant d'implémenter la logique métier, crée **tous les modèles nécessaires et leurs migrations**.

Le MVP doit notamment contenir :

### User

Utilisateur/agent du service client.

### Client

Informations nécessaires concernant le client.

### Reservation

Doit notamment gérer :

- véhicule ;
- date de début ;
- date de fin ;
- statut ;
- client associé.

### Call / Appel

Doit gérer :

- client ;
- réservation éventuelle ;
- agent ;
- sens : entrant / sortant ;
- motif ;
- date/heure ;
- durée ;
- statut ;
- notes ;
- étiquettes.

---

# 8. CONCEPTION DE LA BASE DE DONNÉES

Conçois une base de données relationnelle propre (PostgreSQL).

Utilise :

- clés étrangères ;
- contraintes appropriées ;
- indexes pertinents ;
- contraintes d'intégrité ;
- types de données adaptés ;
- timestamps ;
- enums/constants lorsqu'ils sont réellement pertinents ;
- relations Eloquent correctement définies.

Ne crée pas d'index arbitrairement.

Chaque index doit être justifié par les requêtes prévues.

Réfléchis notamment aux futures requêtes :

- appels par agent ;
- appels par statut ;
- appels par motif ;
- appels par période ;
- appels par client ;
- réservations par client.

---

# 9. ARCHITECTURE LARAVEL

Utilise les conventions Laravel autant que possible.

Ne crée pas une architecture Enterprise inutilement complexe.

### Principes obligatoires

- Controllers minces ;
- validation avec Form Requests ;
- autorisation avec Policies/Gates lorsque nécessaire ;
- logique métier isolée lorsque sa complexité le justifie ;
- Dependency Injection ;
- Eloquent ;
- Services uniquement lorsqu'ils apportent une vraie valeur ;
- Resource classes lorsque pertinent ;
- Events/Listeners uniquement lorsque justifiés ;
- Jobs uniquement lorsque réellement nécessaires ;
- configuration via `config/` et `.env` ;
- aucune logique métier importante dans les routes ;
- aucune logique métier importante directement dans les templates Vue.

### Important

**N'utilise pas Repository Pattern, Service Pattern, DTO Pattern, Action Pattern ou une architecture hexagonale uniquement pour donner une apparence "senior".**

Utilise un design pattern lorsqu'il résout réellement un problème.

L'objectif est :

> simplicité + séparation des responsabilités + évolutivité.

---

# 10. POO ET DESIGN PATTERNS

Le code PHP doit respecter les principes fondamentaux de la POO :

- encapsulation ;
- abstraction ;
- polymorphisme lorsque pertinent ;
- composition plutôt qu'héritage inutile ;
- Single Responsibility Principle ;
- Open/Closed Principle ;
- Liskov Substitution Principle ;
- Interface Segregation ;
- Dependency Inversion.

Utilise les Design Patterns seulement lorsqu'ils sont réellement justifiés.

Évite :

- classes gigantesques ;
- God Objects ;
- God Controllers ;
- méthodes de plusieurs centaines de lignes ;
- logique métier dispersée ;
- duplication ;
- abstractions artificielles.

---

# 11. MODÈLES ELOQUENT

Les modèles doivent être conçus proprement.

Utilise correctement :

- relationships ;
- casts ;
- scopes lorsqu'ils simplifient réellement les requêtes ;
- accessors/mutators lorsqu'ils sont pertinents ;
- mass assignment protection ;
- factories ;
- conventions Laravel.

Évite :

- requêtes SQL inutiles ;
- logique complexe dans les modèles ;
- duplication ;
- relations mal définies ;
- N+1 queries.

Lorsque plusieurs relations sont chargées, utilise correctement :

- `with()`
- `load()`
- `loadMissing()`

selon le contexte.

---

# 12. VALIDATION

Toute donnée provenant du client doit être considérée comme **non fiable**.

Utilise les Form Requests pour les validations complexes.

La validation doit être effectuée côté serveur.

Ne fais jamais confiance aux validations Vue seules.

Vérifie :

- types ;
- formats ;
- longueurs ;
- valeurs autorisées ;
- relations existantes ;
- autorisations ;
- contraintes métier.

---

# 13. AUTORISATION

Ne considère jamais que :

> "le bouton n'existe pas dans Vue"

comme une protection.

Toute opération sensible doit être protégée côté Laravel.

Vérifie les permissions **côté serveur**.

Le frontend ne doit être qu'une couche d'expérience utilisateur.

---

# 14. PERFORMANCE LARAVEL

Conçois le projet avec de bonnes performances dès le départ.

Évite :

- N+1 ;
- requêtes répétées ;
- requêtes dans des boucles ;
- récupération de colonnes inutiles ;
- collections énormes inutilement chargées en mémoire ;
- traitements coûteux côté PHP lorsqu'ils peuvent être réalisés efficacement par la base.

Utilise intelligemment :

- eager loading ;
- pagination ;
- scopes ;
- indexes ;
- agrégations SQL ;
- `select()` lorsque pertinent ;
- cache uniquement lorsque nécessaire.

Ne fais pas d'optimisation prématurée.

---

# 15. DASHBOARD

Le dashboard doit afficher :

- volume d'appels par jour/semaine ;
- répartition par motif ;
- répartition par statut ;
- durée moyenne ;
- classement des agents ;
- graphique.

Les statistiques doivent être calculées efficacement.

Évite de charger tous les appels en PHP puis de faire les calculs avec des boucles si une agrégation SQL permet de faire le travail efficacement.

Réfléchis notamment à :

- `COUNT`
- `AVG`
- `GROUP BY`
- filtrage par dates ;
- indexes ;
- pagination lorsque nécessaire.

---

# 16. VUE.JS

Utilise :

- Vue 3 ;
- Composition API ;
- TypeScript ;
- composants réutilisables ;
- props typées ;
- événements clairement définis ;
- `computed` lorsque pertinent ;
- `watch` uniquement lorsque nécessaire.

Respecte les bonnes pratiques officielles Vue.

Évite :

- composants gigantesques ;
- logique métier dans les composants ;
- duplication ;
- `any` inutile ;
- état global inutile ;
- watchers excessifs ;
- manipulations DOM manuelles inutiles ;
- `innerHTML` lorsque non nécessaire ;
- templates dynamiques non fiables.

---

# 17. STRUCTURE FRONTEND

Construis une structure claire et maintenable.

Par exemple, adapte intelligemment l'organisation autour de :

```text
resources/
└── js/
    ├── Components/
    ├── Layouts/
    ├── Pages/
    ├── Composables/
    ├── Types/
    └── Utils/
```

Ne crée pas des dossiers simplement pour respecter cette structure.

La structure finale doit refléter réellement les responsabilités du projet.

---

# 18. UX

L'interface doit être :

- simple ;
- claire ;
- responsive ;
- professionnelle ;
- rapide.

Pas besoin d'un design extravagant.

Priorité :

1. fonctionnalité ;
2. clarté ;
3. cohérence ;
4. responsive ;
5. performance.

---

# 19. SÉCURITÉ — SECURITY BY DESIGN

La sécurité doit être intégrée **dès la conception**, et non ajoutée à la fin.

Analyse notamment :

### Laravel

- CSRF ;
- XSS ;
- SQL Injection ;
- Mass Assignment ;
- IDOR/BOLA ;
- authentification ;
- autorisation ;
- sessions ;
- cookies ;
- validation ;
- rate limiting ;
- headers HTTP ;
- CORS si nécessaire ;
- gestion des erreurs ;
- secrets ;
- `.env` ;
- logs ;
- uploads éventuels.

### PHP

Évite notamment :

- `eval()` ;
- exécution arbitraire ;
- désérialisation dangereuse ;
- commandes shell inutiles ;
- données non validées ;
- secrets hardcodés.

### Vue

Respecte notamment :

- aucune donnée utilisateur utilisée comme template Vue ;
- aucune donnée non fiable injectée directement dans HTML ;
- aucune clé secrète dans le frontend ;
- aucune logique de sécurité uniquement côté frontend.

---

# 20. PRINCIPE FONDAMENTAL DE SÉCURITÉ

Le frontend est **non fiable**.

Tout ce qui vient de Vue peut être manipulé par un utilisateur.

Donc :

```text
Vue = UX
Laravel = Security + Business Rules
Database = Integrity
```

Toute règle métier importante doit être vérifiée côté Laravel.

---

# 21. BONUS — À METTRE EN STANDBY

Le brief précise explicitement que les bonus sont facultatifs et qu'un MVP propre est prioritaire.

**N'implémente aucun bonus avant que le MVP obligatoire soit terminé, testé et propre.**

Les bonus à garder en attente sont :

- IA pour résumé/sentiment ;
- notifications pour appels urgents ;
- API REST ;
- tests automatisés supplémentaires.

Tu peux préparer l'architecture pour qu'ils soient facilement ajoutables plus tard, mais **ne les développe pas maintenant**.

---

# 22. TESTS

Prépare dès maintenant l'infrastructure de tests.

Utilise la solution adaptée à la version Laravel choisie :

- PHPUnit ou Pest.

Les fonctionnalités critiques devront ensuite être testées :

- authentification ;
- création d'un appel ;
- validation ;
- autorisation ;
- filtres ;
- rattachement réservation ;
- statistiques.

---

# 23. QUALITÉ ET OUTILLAGE

Utilise les outils officiels/adaptés lorsque pertinents :

### Backend

- Laravel Pint ;
- PHPUnit/Pest ;
- outils Laravel appropriés.

### Frontend

- TypeScript ;
- ESLint ;
- Prettier si pertinent.

Le code doit être formaté et cohérent.

---

# 24. COMMENTAIRES

Je ne veux **pas de commentaires inutiles**.

Interdit :

```php
// Get the user
$user = User::find($id);
```

Commente uniquement :

- une logique réellement complexe ;
- une décision technique non évidente ;
- une contrainte particulière ;
- un comportement qui ne serait pas compréhensible autrement.

Le code doit être suffisamment clair par lui-même.

---

# 25. COMPLEXITÉ

Je veux des implémentations avec une bonne complexité temporelle et spatiale.

Mais :

**ne sacrifie jamais la lisibilité, les conventions Laravel ou la maintenabilité pour une micro-optimisation.**

Privilégie :

- traitement en base lorsque pertinent ;
- pagination ;
- structures de données adaptées ;
- requêtes efficaces ;
- réduction des données transférées ;
- absence de boucles inutiles ;
- absence de recalculs inutiles.

---

# 26. GIT

Le brief exige des commits clairs et progressifs.

Ne fais surtout pas un énorme commit final.

Pour cette première phase, prépare des commits logiques tels que :

```text
chore: initialize Laravel application
chore: configure Vue and Inertia
chore: configure authentication
chore: configure database and testing
chore: add initial domain models and migrations
```

Adapte les messages à ce qui a réellement été effectué.

Ne committe jamais :

- `.env` ;
- secrets ;
- credentials ;
- fichiers sensibles.

---

# 27. README

Prépare déjà un README professionnel contenant au minimum :

- présentation du projet ;
- stack technique ;
- prérequis ;
- installation ;
- configuration `.env` ;
- migration ;
- lancement local ;
- tests ;
- lint/format ;
- structure générale ;
- choix architecturaux importants.

Ne prétends pas qu'une fonctionnalité existe si elle n'est pas encore implémentée.

Le README devra être complété progressivement.

---

# 28. CE QUI DOIT ÊTRE FAIT MAINTENANT

Pour cette première étape, réalise UNIQUEMENT :

### Backend

- initialisation Laravel ;
- configuration ;
- authentification ;
- configuration DB ;
- modèles ;
- relations ;
- migrations ;
- factories ;
- seeders de base si nécessaire ;
- configuration des tests ;
- configuration qualité du code.

### Frontend

- Vue 3 ;
- TypeScript ;
- Inertia ;
- Vite ;
- structure initiale des composants ;
- layout de base ;
- configuration qualité du code.

### Infrastructure projet

- `.env.example` ;
- `.gitignore` ;
- README initial ;
- configuration Git ;
- configuration des outils de qualité.

---

# 29. CE QUI NE DOIT PAS ÊTRE FAIT MAINTENANT

Ne développe pas encore :

- dashboard complet ;
- gestion complète des appels ;
- filtres ;
- statistiques ;
- bonus IA ;
- notifications ;
- API REST ;
- fonctionnalités avancées.

Nous les développerons **après validation de cette première version**.

---

# 30. GATE OBLIGATOIRE — ARRÊT APRÈS SETUP

Lorsque cette première phase est terminée :

1. vérifie que le projet démarre correctement ;
2. vérifie que Laravel fonctionne ;
3. vérifie que Vue fonctionne ;
4. vérifie qu'Inertia fonctionne ;
5. vérifie l'authentification ;
6. vérifie les migrations ;
7. vérifie les modèles et relations ;
8. vérifie les seeders/factories ;
9. lance les tests disponibles ;
10. lance le formatter/linter ;
11. vérifie qu'aucun secret n'est présent ;
12. vérifie que le projet est propre.

Ensuite :

**ARRÊTE-TOI.**

Ne commence aucune fonctionnalité métier supplémentaire.

Je veux d'abord inspecter cette première version et la pousser moi-même sur GitHub.

---

# 31. RAPPORT FINAL DE CETTE PHASE

À la fin, donne-moi uniquement un rapport synthétique contenant :

### Stack choisie

- version Laravel ;
- version PHP ;
- version Vue ;
- TypeScript ;
- Inertia ;
- base de données ;
- solution d'authentification.

### Architecture

Explique brièvement pourquoi cette architecture a été choisie.

### Structure

Présente les principaux dossiers créés.

### Base de données

Liste :

- modèles ;
- relations ;
- migrations ;
- indexes importants.

### Sécurité

Liste les principales mesures déjà mises en place.

### Tests

Indique les tests exécutés et leur résultat.

### Commandes

Donne les commandes nécessaires pour :

- installer ;
- lancer ;
- migrer ;
- seed ;
- tester ;
- formatter ;
- lancer le frontend.

### Git

Indique les commits réalisés.

### État

Termine impérativement par :

**SETUP PHASE COMPLETE — WAITING FOR USER GITHUB PUSH AND APPROVAL.**

CONTRAINTE FONDAMENTALE — RESPECT STRICT DU SUJET

Tu dois respecter **strictement toutes les contraintes techniques de l'exercice**.

Le sujet impose :

- **Laravel 10+** comme framework backend/application principal ;
- **PHP moderne** ;
- **Eloquent ORM** pour l'accès aux données ;
- Front-end libre parmi **Blade, Vue.js ou Livewire** ;
- Base de données parmi **MySQL, PostgreSQL ou SQLite** ;
- Git avec des commits clairs et progressifs.

### Choix d'architecture

Pour le front-end, choisis **Vue.js**.

Utilise donc une architecture :

**Laravel + Eloquent + Inertia.js + Vue 3 + TypeScript**

Laravel doit rester **l'application principale** : routes, authentification, autorisation, validation, logique métier, Eloquent, migrations, modèles, accès aux données et sécurité.

Vue.js est uniquement utilisé pour construire l'interface utilisateur.

**Ne crée PAS deux applications totalement indépendantes Laravel Backend + Vue SPA.**
Il ne doit pas y avoir un backend séparé de type API-only et une SPA indépendante si cela n'est pas nécessaire à l'exercice.

Inertia.js peut être utilisé comme couche de liaison entre Laravel et Vue, sans transformer le projet en deux applications distinctes.

### Règle de conformité

À chaque décision technique, vérifie :

1. que Laravel reste au cœur de l'application ;
2. que les données sont manipulées avec **Eloquent ORM** ;
3. que les migrations Laravel définissent correctement le schéma ;
4. que la logique métier reste côté Laravel ;
5. que Vue respecte simplement le choix de front-end autorisé par le sujet ;
6. qu'aucune technologie ne remplace ou contourne les contraintes imposées par l'exercice.

Si une décision technique risque de sortir du périmètre imposé par le sujet, **ne la prends pas sans justification**.

### Version Laravel

Utilise une version **Laravel 10 ou supérieure actuellement supportée**, en privilégiant une version stable et supportée plutôt qu'une ancienne version uniquement parce que le sujet mentionne Laravel 10+.

Avant l'installation, vérifie la compatibilité entre :

- version de Laravel ;
- version de PHP ;
- version de Vue ;
- version d'Inertia ;
- Node.js ;
- Vite ;
- dépendances utilisées.

Ne downgrade aucune technologie sans raison technique.

### Principe architectural

**Laravel = application principale, sécurité, backend, métier et données.**

**Eloquent = accès aux données et relations.**

**Vue.js = interface utilisateur.**

**Inertia.js = communication Laravel ↔ Vue.**

Cette architecture doit rester simple, cohérente avec le sujet et adaptée à un MVP de 6–10 heures.

Ne sur-architecture pas le projet.

**N'avance pas vers la phase suivante tant que je ne t'ai pas explicitement donné mon accord après le push GitHub.**

---

# CONSIGNE FINALE

Tu dois agir comme un **développeur Laravel/Vue senior responsable de la qualité globale du projet**.

Ne cherche pas à produire énormément de code.

Cherche à produire **la bonne architecture, le bon code et les bonnes fondations**.

Je préfère :

> un petit projet extrêmement propre, sécurisé, cohérent et maintenable

plutôt que :

> beaucoup de fonctionnalités avec une architecture médiocre.

**Commence par analyser le brief, vérifie les recommandations officielles actuelles, initialise le projet et construis uniquement cette première fondation.**

**À la fin du setup, arrête-toi et attends mon accord.**
