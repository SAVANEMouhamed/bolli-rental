# Pack Claude Code — Bolli Rental

Configuration d'agent du projet. Tout est versionné et partagé via Git, sauf `settings.local.json`.

## Contenu

```
.claude/
├── CLAUDE.md              instructions projet, chargées à chaque session
├── settings.json          permissions + hooks (partagé avec l'équipe)
├── rules/                 règles chargées automatiquement
│   ├── 00-perimetre-conformite.md   toujours chargée — la contrainte fondamentale
│   ├── php-laravel.md               app/, routes/, config/, database/
│   ├── vue-inertia.md               resources/js/
│   ├── database.md                  migrations, modèles
│   └── tests.md                     tests/
├── skills/                procédures chargées à la demande
├── agents/                sous-agents spécialisés
└── hooks/                 scripts exécutés par le harnais, hors décision du modèle
```

## Trois niveaux, trois usages

| Mécanisme              | Chargement                                                | Rôle                                                           |
| ---------------------- | --------------------------------------------------------- | -------------------------------------------------------------- |
| `CLAUDE.md` + `rules/` | à chaque session, ou à l'ouverture des fichiers concernés | faits et conventions permanents                                |
| `skills/`              | à la demande                                              | procédures, checklists, runbooks                               |
| `hooks/`               | à chaque événement                                        | **contrainte réelle** — s'applique quoi qu'en décide le modèle |

Un `CLAUDE.md` est un contexte, pas une garantie d'exécution. Ce qui doit être infaillible vit dans les hooks.

## Skills

**Automatiques** — Claude les charge quand ils sont pertinents :
`scope-guard` · `laravel-backend` · `eloquent-database` · `query-performance` · `vue-inertia-ui` · `ui-ux` · `security-owasp` · `testing-pest` · `dashboard-analytics` · `quality-gate` · `project-readme`

**Manuels uniquement** (`disable-model-invocation: true` — effets de bord ou décision de phase) :
`/setup-phase1` · `/git-commits` · `/phase-gate` · `/deploy-laravel-cloud`

## Agents

`@agent-laravel-architect` — conception avant code, lecture seule, rend un plan.
`@agent-security-auditor` — audit OWASP du diff, lecture seule, rend des constats hiérarchisés.

## Hooks

| Événement                   | Script             | Effet                                                                                                                                                       |
| --------------------------- | ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `SessionStart`              | `session-start.sh` | injecte l'état réel du dépôt et de la stack                                                                                                                 |
| `UserPromptSubmit`          | `prompt-scope.sh`  | réinjecte le contrat de périmètre, alerte sur les demandes de bonus                                                                                         |
| `PreToolUse` / Bash         | `guard-bash.sh`    | refuse suppression de racine, push forcé, réinitialisation destructive, permissions trop larges, script distant exécuté en pipe, écriture shell dans `.env` |
| `PreToolUse` / Edit\|Write  | `guard-write.sh`   | refuse l'écriture dans `.env` et les fichiers de clés, refuse un secret en clair                                                                            |
| `PostToolUse` / Edit\|Write | `post-edit.sh`     | Pint / Prettier, signale les `dd()`, `console.log`, `any`, `v-html`, `strict_types` manquant                                                                |

`guard-bash.sh` n'analyse que les commandes en **position d'exécution** : un fichier qui mentionne une commande dangereuse dans sa documentation n'est pas bloqué.

Les hooks d'un `settings.json` de projet exigent d'avoir accepté la confiance de l'espace de travail au premier lancement.

## Activation

Skills et hooks d'un dossier créé après le démarrage de la session ne sont pris en compte qu'au **redémarrage** de Claude Code. Ensuite :

```
/context      # .claude/CLAUDE.md et les rules doivent apparaître sous « Memory files »
/doctor       # diagnostic de configuration
/             # les skills du projet apparaissent dans le menu
```

## Cohabitation avec Laravel Boost

`php artisan boost:install` génère un `CLAUDE.md` **à la racine** et des skills préfixés par package. Les instructions du projet vivent dans `.claude/CLAUDE.md` pour ne pas être écrasées ; les deux fichiers se chargent ensemble. En cas de contradiction, le cahier des charges et les règles de ce dossier priment.

## Maintenance

- Une correction répétée deux fois → l'écrire dans `CLAUDE.md` ou dans la règle concernée.
- Une procédure multi-étapes → un skill, pas une ligne de `CLAUDE.md`.
- Une contrainte qui doit tenir même quand le modèle se trompe → un hook.
- Garder `.claude/CLAUDE.md` sous 200 lignes : au-delà, l'adhérence baisse.
