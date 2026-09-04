---
name: vue-inertia-ui
description: Conventions Vue 3 + TypeScript + Inertia 3 du projet — structure, props typées, formulaires, composables, communication Laravel ↔ Vue.
when_to_use: Avant d'écrire ou modifier un composant Vue, une page Inertia, un composable, un type TypeScript, ou de brancher un formulaire sur un contrôleur Laravel.
paths:
    - 'resources/js/**'
---

## Structure (celle du starter kit Vue officiel, en minuscules)

```
resources/js/
├── components/     # composants réutilisables (+ components/ui/ = shadcn-vue)
├── composables/    # logique de vue réutilisable
├── layouts/        # AppLayout, AuthLayout
├── lib/            # utilitaires, helpers de formatage
├── pages/          # une page = une réponse Inertia::render('calls/Index')
└── types/          # interfaces partagées avec le backend
```

Ne pas renommer en `Components/`, `Pages/` : les alias Vite et les imports du starter kit suivent cette casse. Ne pas créer de dossier tant qu'il n'a pas de contenu réel.

## Contrat de page

```vue
<script setup lang="ts">
import type { Call, Paginated, CallFilters } from '@/types';

const props = defineProps<{
    calls: Paginated<Call>;
    filters: CallFilters;
}>();
</script>
```

Les types de `resources/js/types/` reflètent exactement ce que le contrôleur envoie. Quand le contrôleur change de forme, le type change dans le même commit.

## Formulaires

```vue
const form = useForm({ client_id: null as number | null, direction: 'inbound' })
form.post(store().url, { preserveScroll: true, onSuccess: () => form.reset() })
```

- Erreurs affichées depuis `form.errors.<champ>` — elles viennent de la validation serveur, seule source de vérité.
- Bouton désactivé pendant `form.processing`.
- Pas de `fetch`/`axios` manuel pour ce qu'Inertia fait déjà.
- Routes typées via Wayfinder plutôt que des chaînes d'URL en dur.

## Filtres de liste

Un composable `useFilters` qui pousse les filtres dans l'URL avec `router.get(..., { preserveState: true, replace: true })` et un debounce sur la recherche texte. L'URL reste partageable et rechargeable — c'est aussi ce qui rend les filtres testables côté serveur.

## Règles

- `<script setup lang="ts">` partout, Composition API uniquement.
- `computed` pour l'état dérivé ; `watch` seulement pour un effet de bord réel.
- Pas d'état global (Pinia) tant qu'aucun état n'est réellement partagé entre pages non liées — les props Inertia suffisent au MVP.
- Composant > ~200 lignes ou > 2 responsabilités : découper.
- Aucune règle métier ni de sécurité côté Vue : masquer un bouton est cosmétique, la Policy Laravel décide.
- Jamais `v-html` sur une donnée utilisateur, jamais de template compilé depuis une chaîne, jamais de secret dans le bundle.
