#!/usr/bin/env bash
# PreToolUse/Edit|Write — protège les fichiers sensibles et bloque les secrets en clair.
#
# Modèle : un .env LOCAL contenant des identifiants de conteneur jetable n'est pas un secret.
# Ce qui compte vraiment : (1) aucun .env d'un environnement distant touché, (2) aucune clé
# réelle écrite où que ce soit, (3) aucun .env dans Git (cf. guard-bash.sh + .gitignore).
set -uo pipefail
payload=$(cat)
file=$(printf '%s' "$payload" | jq -r '.tool_input.file_path // ""' 2>/dev/null)
body=$(printf '%s' "$payload" | jq -r '(.tool_input.content // .tool_input.new_string // "")' 2>/dev/null)

deny() {
  jq -nc --arg r "$1" '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"deny",permissionDecisionReason:$r}}'
  exit 0
}

case "$file" in
  *.env.production|*.env.prod|*.env.staging|*.env.live)
    deny "Fichier d'environnement distant. Les secrets de production se configurent dans l'interface Laravel Cloud, jamais dans un fichier du dépôt." ;;
  *.pem|*.key|*.p12|*.pfx|*id_rsa*|*id_ed25519*)
    deny "Écriture dans un fichier de clé refusée." ;;
esac

# Une vraie clé d'API ou une clé privée n'a sa place dans aucun fichier, .env compris.
if printf '%s' "$body" | grep -Eq '(sk-[A-Za-z0-9_-]{20,}|sk-ant-[A-Za-z0-9_-]{20,}|AKIA[0-9A-Z]{16}|gh[pous]_[A-Za-z0-9]{20,}|xox[baprs]-[A-Za-z0-9-]{20,}|-----BEGIN [A-Z ]*PRIVATE KEY-----)'; then
  deny "Un secret en clair a été détecté dans le contenu écrit. Le placer dans .env (local) ou dans l'interface de l'hébergeur (production), jamais dans un fichier versionné."
fi

case "$file" in
  *.env)
    jq -nc '{systemMessage:"Écriture dans .env — vérifier que .gitignore le couvre et que la valeur reste locale."}' ;;
esac
exit 0
