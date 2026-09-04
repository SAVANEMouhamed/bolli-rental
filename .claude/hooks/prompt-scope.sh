#!/usr/bin/env bash
# UserPromptSubmit — réinjecte le contrat de périmètre à chaque tour, et alerte sur les demandes de bonus.
set -uo pipefail
payload=$(cat)
prompt=$(printf '%s' "$payload" | jq -r '.prompt // ""' 2>/dev/null | tr '[:upper:]' '[:lower:]')

echo "[Contrat Bolli Rental] Laravel = application principale (routes, auth, autorisation, validation, métier). Eloquent = données. Vue = UI seulement. Inertia = liaison, pas une SPA séparée. MVP avant tout bonus. Commits progressifs. Décision technique non triviale ou nouvelle dépendance => passer par le skill scope-guard."

case "$prompt" in
  *" ia "*|*"intelligence artificielle"*|*transcript*|*sentiment*|*"résumé automatique"*|*openai*|*anthropic*|*"api rest"*|*notification*|*webhook*|*"appli mobile"*|*sanctum*)
    echo "[Alerte périmètre] La demande touche un BONUS explicitement mis en attente par le sujet (IA, notifications, API REST). Invoquer scope-guard et obtenir un accord explicite avant d'implémenter quoi que ce soit."
    ;;
esac
exit 0
