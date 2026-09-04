#!/usr/bin/env bash
# PostToolUse/Edit|Write — formate le fichier touché et signale les oublis de debug.
set -uo pipefail
cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0
file=$(cat | jq -r '.tool_input.file_path // ""' 2>/dev/null)
[ -f "$file" ] || exit 0

notes=""

case "$file" in
  *.php)
    [ -x vendor/bin/pint ] && vendor/bin/pint --quiet "$file" >/dev/null 2>&1
    grep -nE '(^|[^a-zA-Z_])(dd|dump|var_dump|ray)\(' "$file" >/dev/null 2>&1 \
      && notes="${notes}Debug oublié (dd/dump/var_dump) dans $(basename "$file"). "
    grep -q 'declare(strict_types=1)' "$file" 2>/dev/null || \
      case "$file" in *app/*|*routes/*|*tests/*) notes="${notes}declare(strict_types=1) manquant dans $(basename "$file"). ";; esac
    ;;
  *.vue|*.ts)
    [ -d node_modules ] && npx --no-install prettier --write "$file" >/dev/null 2>&1
    grep -n 'console\.log(' "$file" >/dev/null 2>&1 \
      && notes="${notes}console.log oublié dans $(basename "$file"). "
    grep -nE ':\s*any\b|as any\b' "$file" >/dev/null 2>&1 \
      && notes="${notes}Type 'any' dans $(basename "$file") — typer ou justifier en commentaire. "
    grep -n 'v-html' "$file" >/dev/null 2>&1 \
      && notes="${notes}ALERTE XSS : v-html dans $(basename "$file") — interdit sur une donnée utilisateur. "
    ;;
esac

[ -n "$notes" ] && jq -nc --arg m "$notes" '{systemMessage:$m}'
exit 0
