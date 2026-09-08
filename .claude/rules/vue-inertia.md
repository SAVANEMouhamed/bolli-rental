---
paths:
    - 'resources/js/**/*.vue'
    - 'resources/js/**/*.ts'
---

# Règles Vue 3 / TypeScript / Inertia

- `<script setup lang="ts">` systématiquement. Composition API uniquement.
- Props typées via `defineProps<T>()`, events via `defineEmits<T>()`. Interfaces partagées dans `resources/js/types/`.
- Pas de `any` sans commentaire justifiant l'impossibilité de typer.
- Respecter l'arborescence générée par le starter kit Vue officiel : `components/`, `composables/`, `layouts/`, `lib/`, `pages/`, `types/` (minuscules). Ne pas créer de dossier vide « au cas où ».
- Un composant > ~200 lignes ou avec plus de 2 responsabilités se découpe.
- Formulaires : `useForm` d'Inertia (erreurs serveur, état `processing`, `reset`). Pas de `fetch`/`axios` manuel pour ce que fait Inertia.
- `computed` pour tout état dérivé. `watch` uniquement pour un effet de bord réel, jamais pour recalculer une valeur.
- Zéro règle métier ou de sécurité côté Vue : elle est décorative, elle est rejouée côté Laravel.
- Jamais de `v-html` sur une donnée utilisateur. Jamais de template compilé à partir d'une chaîne dynamique. Jamais de secret ou de clé d'API dans le bundle.
- Pas de manipulation DOM manuelle quand une directive ou une ref réactive suffit.

## Piège — régénération Wayfinder

Le plugin Vite est configuré avec `formVariants: true`. Une régénération manuelle
doit donc passer `--with-form` :

```bash
php artisan wayfinder:generate --with-form
```

Sans ce drapeau, les helpers `.form()` disparaissent et une douzaine de composants
du starter kit ne compilent plus (`Property 'form' does not exist`).
