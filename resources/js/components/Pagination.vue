<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

const props = defineProps<{
    // Seules les métadonnées de pagination sont utilisées : le type générique
    // évite d'imposer une entité particulière aux écrans qui l'emploient.
    meta: Paginated<unknown>['meta'];
    links: Paginated<unknown>['links'];
    label: string;
}>();
</script>

<template>
    <nav
        v-if="props.meta.total > 0"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
        :aria-label="`Pagination des ${label}`"
    >
        <p class="text-muted-foreground text-sm" aria-live="polite">
            {{ props.meta.from }}–{{ props.meta.to }} sur
            <span class="text-foreground font-medium">{{
                props.meta.total
            }}</span>
            {{ label }}
        </p>

        <ul v-if="props.meta.last_page > 1" class="flex flex-wrap gap-1">
            <li v-for="link in props.links" :key="link.label">
                <span
                    v-if="link.url === null"
                    class="text-muted-foreground inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm opacity-50"
                    v-text="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    :aria-current="link.active ? 'page' : undefined"
                    :class="
                        cn(
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors',
                            link.active
                                ? 'bg-primary text-primary-foreground border-primary'
                                : 'hover:bg-accent',
                        )
                    "
                    v-text="link.label"
                />
            </li>
        </ul>
    </nav>
</template>
