#!/usr/bin/env bash
# PreToolUse/Bash — refuse les commandes destructrices ou qui exfiltrent des secrets.
# Le motif doit être en POSITION DE COMMANDE (début de segment), sinon un fichier qui
# se contente de mentionner une commande dangereuse serait bloqué à tort.
set -uo pipefail
cmd=$(cat | jq -r '.tool_input.command // ""' 2>/dev/null)

deny() {
  jq -nc --arg r "$1" '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"deny",permissionDecisionReason:$r}}'
  exit 0
}

# Corps des heredocs retiré : c'est de la donnée écrite, pas du code exécuté.
stripped=$(printf '%s\n' "$cmd" | sed -E 's/<<-?[[:space:]]*.?[A-Za-z_][A-Za-z0-9_]*.?.*$//')

# Un segment par commande réellement invoquée.
segments=$(printf '%s\n' "$stripped" | sed -E 's/[;&|]+/\n/g' | sed -E 's/^[[:space:]]*(sudo|env|nohup|time)[[:space:]]+//')

seg() { printf '%s\n' "$segments" | grep -qE "^[[:space:]]*$1"; }

seg 'rm[[:space:]]+(-[a-zA-Z]*[rR][a-zA-Z]*[fF]|-[a-zA-Z]*[fF][a-zA-Z]*[rR])[a-zA-Z]*[[:space:]]+(/|~|\$HOME|\*)[[:space:]]*$' \
  && deny "Suppression récursive d'une racine (/, ~, *). Refusé."
seg 'git[[:space:]]+push\b.*(--force|-f)\b' \
  && deny "Push forcé refusé : le projet exige un historique Git clair et progressif."
seg 'git[[:space:]]+reset[[:space:]]+--hard' \
  && deny "Cette commande détruit le travail non commité. Committer d'abord, ou obtenir une confirmation explicite de l'utilisateur."
seg 'git[[:space:]]+clean[[:space:]]+-[a-zA-Z]*[fd]' \
  && deny "Suppression de fichiers non suivis. Refusé sans confirmation explicite."
seg 'git[[:space:]]+add\b.*\.env' \
  && deny "Le fichier .env ne doit jamais entrer dans Git."
seg 'chmod[[:space:]]+(-[a-zA-Z]+[[:space:]]+)*777\b' \
  && deny "Permissions trop permissives (777). Utiliser des permissions restreintes."
seg '(curl|wget)\b' && printf '%s\n' "$stripped" | grep -qE '\|[[:space:]]*(sudo[[:space:]]+)?(ba)?sh\b' \
  && deny "Exécution d'un script distant non vérifié (pipe vers un shell). Refusé."

printf '%s\n' "$stripped" | grep -qE '(>[[:space:]]*|tee[[:space:]]+(-a[[:space:]]+)?)(\./)?\.env([[:space:]]|$)' \
  && deny "Écriture directe dans .env via le shell. Modifier .env.example, ou demander à l'utilisateur de renseigner sa valeur."

exit 0
