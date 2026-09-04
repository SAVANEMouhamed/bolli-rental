---
name: git-commits
description: Découpe et rédige les commits du projet, en commits progressifs et lisibles.
disable-model-invocation: true
argument-hint: '[portée optionnelle]'
allowed-tools: Bash(git status:*) Bash(git diff:*) Bash(git log:*) Bash(git add:*) Bash(git commit:*) Bash(git restore:*)
---

Le cahier des charges exige des commits « clairs et progressifs » et proscrit le commit unique final. L'historique fait partie de la livraison.

## Procédure

1. `git status` et `git diff` pour voir l'état réel.
2. **Vérifier qu'aucun secret ne part** : `.env`, clés, tokens, dumps de base, `node_modules`, `vendor`. En cas de doute, ne pas committer et le signaler.
3. Regrouper les changements en unités cohérentes : une intention = un commit. Si le travail en cours mélange plusieurs intentions, faire plusieurs commits avec `git add` sélectif.
4. Rédiger les messages, committer, puis résumer ce qui a été fait.

## Format

Conventional Commits, sujet à l'impératif, ≤ 72 caractères, en anglais pour rester cohérent avec l'écosystème.

```
feat(calls): add call filtering by agent, status and reason
fix(dashboard): correct average duration on empty period
chore(setup): configure Pint, ESLint and Prettier
refactor(calls): move filter logic into an Eloquent scope
test(calls): cover authorization on call detail
docs(readme): document local installation and demo account
```

Types utilisés : `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `perf`, `style`.

Corps de message seulement quand le _pourquoi_ n'est pas évident depuis le diff. Pas de corps qui redit le sujet.

## Règles

- Un commit doit laisser le projet dans un état cohérent : la suite de tests passe, le build passe.
- Jamais de `git push --force` sur `main`.
- Jamais `git add .` sans avoir lu `git status` juste avant.
- Ne pas committer de code commenté, de `dd()`, de `console.log`.
- Ne pas pousser sans y avoir été invité explicitement.
