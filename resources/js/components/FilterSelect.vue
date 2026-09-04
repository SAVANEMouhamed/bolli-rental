<script setup lang="ts">
import { cn } from '@/lib/utils';

/**
 * `<select>` natif plutôt que le Select shadcn-vue : la barre de filtres en
 * compte six, et le composant natif est déjà accessible au clavier, annoncé par
 * les lecteurs d'écran et confortable sur mobile. Le Select riche reste utilisé
 * là où il apporte une recherche ou un rendu personnalisé.
 */
withDefaults(
    defineProps<{
        options: { value: string | number; label: string }[];
        placeholder?: string;
        /**
         * Un filtre propose « aucune valeur » en tête de liste. Un choix
         * obligatoire — la granularité du tableau de bord — n'en a pas : sans ce
         * drapeau, le placeholder doublonnerait la première option.
         */
        clearable?: boolean;
        id?: string;
        class?: string;
    }>(),
    { placeholder: 'Tous', clearable: true },
);

const model = defineModel<string | number | null>();
</script>

<template>
    <select
        :id="id"
        v-model="model"
        :class="
            cn(
                'border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]',
                $props.class,
            )
        "
    >
        <option v-if="clearable" :value="null">{{ placeholder }}</option>
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>
