<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

const props = defineProps<{
    // Seules les métadonnées de pagination sont utilisées : le type générique
    // évite d'imposer une entité particulière aux écrans qui l'emploient.
    meta: Paginated<unknown>['meta'];
    label: string;
}>();

/**
 * Laravel place toujours le lien « précédent » en tête de `meta.links` et
 * « suivant » en queue ; entre les deux viennent les numéros de page et les
 * ellipses, ces dernières sans URL.
 *
 * Les libellés du framework pour précédent et suivant contiennent des entités
 * HTML (`&laquo;`) : les afficher tels quels donnerait « &laquo; Précédent », et
 * les interpréter demanderait `v-html` sur une chaîne venue du serveur. On pose
 * donc nos propres libellés, et on ne rend en texte que les numéros de page.
 */
const previousLink = computed(() => props.meta.links.at(0) ?? null);
const nextLink = computed(() => props.meta.links.at(-1) ?? null);
const pageLinks = computed(() => props.meta.links.slice(1, -1));

const linkClass = (active: boolean): string =>
    cn(
        'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors',
        active
            ? 'bg-primary text-primary-foreground border-primary'
            : 'hover:bg-accent',
    );
</script>

<template>
    <nav
        v-if="meta.total > 0"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
        :aria-label="`Pagination des ${label}`"
    >
        <p class="text-muted-foreground text-sm" aria-live="polite">
            {{ meta.from }}–{{ meta.to }} sur
            <span class="text-foreground font-medium">{{ meta.total }}</span>
            {{ label }}
        </p>

        <ul v-if="meta.last_page > 1" class="flex flex-wrap items-center gap-1">
            <li>
                <Link
                    v-if="previousLink?.url"
                    :href="previousLink.url"
                    preserve-scroll
                    :class="linkClass(false)"
                    aria-label="Page précédente"
                    rel="prev"
                >
                    <ChevronLeft class="size-4" />
                </Link>
                <span
                    v-else
                    :class="cn(linkClass(false), 'opacity-40')"
                    aria-hidden="true"
                >
                    <ChevronLeft class="size-4" />
                </span>
            </li>

            <li
                v-for="(link, index) in pageLinks"
                :key="link.page ?? `gap-${index}`"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    :class="linkClass(link.active)"
                    :aria-current="link.active ? 'page' : undefined"
                    :aria-label="`Page ${link.label}`"
                >
                    {{ link.label }}
                </Link>
                <span
                    v-else
                    class="text-muted-foreground inline-flex h-8 min-w-8 items-center justify-center px-1 text-sm"
                >
                    …
                </span>
            </li>

            <li>
                <Link
                    v-if="nextLink?.url"
                    :href="nextLink.url"
                    preserve-scroll
                    :class="linkClass(false)"
                    aria-label="Page suivante"
                    rel="next"
                >
                    <ChevronRight class="size-4" />
                </Link>
                <span
                    v-else
                    :class="cn(linkClass(false), 'opacity-40')"
                    aria-hidden="true"
                >
                    <ChevronRight class="size-4" />
                </span>
            </li>
        </ul>
    </nav>
</template>
