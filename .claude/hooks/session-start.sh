#!/usr/bin/env bash
# SessionStart — rappelle l'état réel du projet plutôt que de le laisser deviner.
set -uo pipefail
cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0

echo "== Bolli Rental — état du dépôt =="
if [ -d .git ]; then
  echo "Branche  : $(git rev-parse --abbrev-ref HEAD 2>/dev/null)"
  echo "Racine   : $(git rev-parse --show-toplevel 2>/dev/null)"
  echo "Modifiés : $(git status --porcelain 2>/dev/null | wc -l | tr -d ' ') fichier(s)"
else
  echo "ATTENTION : aucun dépôt Git dans ce dossier. Le sujet exige un dépôt GitHub public dédié avec des commits progressifs. Faire 'git init -b main' avant de committer quoi que ce soit."
fi

if [ -f composer.json ]; then
  echo "Laravel  : $(grep -o '"laravel/framework": *"[^"]*"' composer.json | head -1 | cut -d'"' -f4)"
else
  echo "Phase    : projet Laravel pas encore initialisé — voir /setup-phase1."
fi

[ -f .env ] && git check-ignore -q .env 2>/dev/null || { [ -f .env ] && echo "ALERTE SÉCURITÉ : .env existe et n'est pas ignoré par Git."; }
exit 0
