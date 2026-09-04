<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { EnumValue } from '@/types';

/**
 * La couleur seule exclut les daltoniens : le libellé texte est toujours rendu,
 * la teinte n'est qu'un repère secondaire.
 */
const props = defineProps<{
    value: EnumValue;
    palette?: 'status' | 'direction' | 'reservation';
}>();

const tones: Record<string, string> = {
    resolved:
        'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-900',
    pending:
        'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-900',
    escalated:
        'bg-red-100 text-red-800 border-red-200 dark:bg-red-950 dark:text-red-300 dark:border-red-900',
    inbound:
        'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-950 dark:text-sky-300 dark:border-sky-900',
    outbound:
        'bg-violet-100 text-violet-800 border-violet-200 dark:bg-violet-950 dark:text-violet-300 dark:border-violet-900',
    active: 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-950 dark:text-sky-300 dark:border-sky-900',
    completed:
        'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-900',
    cancelled:
        'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-800',
};

const tone = computed<string>(() => tones[props.value.value] ?? '');
</script>

<template>
    <Badge variant="outline" :class="tone">{{ value.label }}</Badge>
</template>
