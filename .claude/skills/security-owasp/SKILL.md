---
name: security-owasp
description: Security by design pour Bolli Rental — checklist OWASP appliquée à Laravel, PHP et Vue : CSRF, XSS, injection, mass assignment, IDOR, sessions, rate limiting, secrets, logs.
when_to_use: Avant d'écrire une route, un contrôleur, une Policy, un formulaire, une requête de base, un upload ou une intégration externe. Aussi pour auditer une fonctionnalité terminée, ou dès qu'apparaissent les mots authentification, autorisation, secret, token, upload, permission.
---

Principe : **le frontend est non fiable**. `Vue = UX`, `Laravel = sécurité + règles métier`, `Database = intégrité`. Toute donnée venant du navigateur est hostile jusqu'à validation serveur.

La sécurité s'intègre à la conception, elle ne se rajoute pas à la fin.

## Checklist par fonctionnalité

Avant de considérer une fonctionnalité terminée :

- [ ] **Authentification** — route sous middleware `auth`. Aucune page privée accessible déconnecté.
- [ ] **Autorisation (IDOR/BOLA)** — l'objet ciblé appartient bien au périmètre autorisé de l'utilisateur. Un identifiant modifié dans l'URL renvoie 403, pas la donnée d'autrui. Vérifié par une Policy, pas par un `if` dans la vue.
- [ ] **Validation** — Form Request : type, format, longueur, valeurs autorisées (`Rule::enum`), existence des relations (`exists:`), cohérence métier (dates ordonnées, durée positive).
- [ ] **Mass assignment** — `$fillable` explicite, `validated()` en entrée, jamais `all()`. Aucun champ sensible (`role`, `user_id` d'un autre agent) assignable depuis la requête.
- [ ] **Injection SQL** — Eloquent / query builder paramétré. Aucune concaténation dans `whereRaw`/`selectRaw` ; si un fragment brut est indispensable, bindings obligatoires et colonne de tri validée contre une liste blanche.
- [ ] **XSS** — pas de `v-html` sur une donnée utilisateur, pas de Blade `{!! !!}` sur une entrée. L'échappement par défaut suffit.
- [ ] **CSRF** — actif sur toutes les routes de mutation ; jamais désactivé pour « faire marcher » un formulaire.
- [ ] **Rate limiting** — login (`Limit::perMinute(5)->by(email + ip)`), et tout endpoint de recherche ou d'écriture ouvert.
- [ ] **Secrets** — rien en dur dans le code, rien dans le bundle Vue, rien dans Git. `.env` ignoré, `.env.example` sans valeur réelle.
- [ ] **Logs** — aucune donnée personnelle, aucun mot de passe, aucun token, aucun contenu de notes client dans les logs.
- [ ] **Erreurs** — `APP_DEBUG=false` en production, aucune trace ni requête SQL renvoyée au client.
- [ ] **Réponse** — la réponse ne renvoie que les champs nécessaires à l'écran ; pas de `User` complet sérialisé avec son hash.

## Authentification et session

Fortify gère le hashage (bcrypt/argon2), la confirmation de mot de passe et la limitation de tentatives. Vérifier tout de même :

- `SESSION_SECURE_COOKIE=true` et `SESSION_SAME_SITE=lax` en production, `SESSION_HTTP_ONLY=true`.
- Régénération de session à la connexion, invalidation + régénération du token à la déconnexion (Fortify le fait ; ne pas le défaire).
- HTTPS forcé en production.
- Politique de mot de passe : longueur minimale ≥ 12, vérification `uncompromised()`.
- L'enregistrement public est-il souhaitable pour un outil interne ? Par défaut **non** : désactiver `Features::registration()` et créer les agents par seeder/commande. Documenter le choix.

## PHP — interdits

`eval()` · exécution de code arbitraire · `unserialize()` sur une entrée utilisateur · `shell_exec`/`exec` sur une donnée non validée · inclusion de fichier construite depuis une entrée · secret en dur.

## Vue — interdits

Donnée utilisateur utilisée comme template · injection directe dans le HTML · clé d'API ou secret dans le bundle · logique de sécurité uniquement côté client · confiance en une valeur cachée d'un formulaire.

## Uploads (si un bonus les introduit)

Type MIME vérifié côté serveur, extension en liste blanche, taille limitée, nom de fichier régénéré, stockage hors du dossier public, jamais servi en exécution.

## Audit

Pour un audit complet des changements en cours, `/security-review` (skill fourni par Claude Code) complète cette checklist.
