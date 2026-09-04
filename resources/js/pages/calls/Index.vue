<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PhoneOff, Plus, X } from '@lucide/vue';
import { computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterSelect from '@/components/FilterSelect.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useFilters } from '@/composables/useFilters';
import { formatDateTime, formatDuration } from '@/lib/format';
import { create, index, show } from '@/routes/calls';
import type { Call, CallFilterOptions, CallFilters, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Appels', href: index() }],
    },
});

const props = defineProps<{
    calls: Paginated<Call>;
    filters: CallFilters;
    options: CallFilterOptions;
}>();

const { filters, reset, hasActiveFilters } = useFilters(
    {
        search: props.filters.search ?? '',
        agent: props.filters.agent ?? null,
        status: props.filters.status ?? null,
        reason: props.filters.reason ?? null,
        direction: props.filters.direction ?? null,
        tag: props.filters.tag ?? null,
        from: props.filters.from ?? '',
        to: props.filters.to ?? '',
    },
    index().url,
    { debounced: ['search'] },
);

const agentOptions = computed(() =>
    props.options.agents.map((agent) => ({
        value: agent.id,
        label: agent.name,
    })),
);

const tagOptions = computed(() =>
    props.options.tags.map((tag) => ({ value: tag.slug, label: tag.name })),
);

const openCall = (call: Call): void => {
    router.visit(show(call.id).url);
};
</script>

<template>
    <Head title="Appels" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <Heading
                title="Appels"
                description="Historique du service client, filtrable par agent, statut, motif et période."
            />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Enregistrer un appel
                </Link>
            </Button>
        </div>

        <section
            class="bg-card grid gap-3 rounded-xl border p-4 md:grid-cols-2 lg:grid-cols-4"
            aria-label="Filtres"
        >
            <div class="grid gap-1.5 lg:col-span-2">
                <Label for="filter-search">Recherche</Label>
                <Input
                    id="filter-search"
                    v-model="filters.search"
                    type="search"
                    placeholder="Nom du client, téléphone, notes…"
                />
            </div>

            <div class="grid gap-1.5">
                <Label for="filter-agent">Agent</Label>
                <FilterSelect
                    id="filter-agent"
                    v-model="filters.agent"
                    :options="agentOptions"
                    placeholder="Tous les agents"
                />
            </div>

            <div class="grid gap-1.5">
                <Label for="filter-status">Statut</Label>
                <FilterSelect
                    id="filter-status"
                    v-model="filters.status"
                    :options="options.statuses"
                    placeholder="Tous les statuts"
                />
            </div>

            <div class="grid gap-1.5">
                <Label for="filter-reason">Motif</Label>
                <FilterSelect
                    id="filter-reason"
                    v-model="filters.reason"
                    :options="options.reasons"
                    placeholder="Tous les motifs"
                />
            </div>

            <div class="grid gap-1.5">
                <Label for="filter-direction">Sens</Label>
                <FilterSelect
                    id="filter-direction"
                    v-model="filters.direction"
                    :options="options.directions"
                    placeholder="Entrants et sortants"
                />
            </div>

            <div class="grid gap-1.5">
                <Label for="filter-tag">Étiquette</Label>
                <FilterSelect
                    id="filter-tag"
                    v-model="filters.tag"
                    :options="tagOptions"
                    placeholder="Toutes les étiquettes"
                />
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="grid gap-1.5">
                    <Label for="filter-from">Du</Label>
                    <Input
                        id="filter-from"
                        v-model="filters.from"
                        type="date"
                    />
                </div>
                <div class="grid gap-1.5">
                    <Label for="filter-to">Au</Label>
                    <Input id="filter-to" v-model="filters.to" type="date" />
                </div>
            </div>

            <div
                v-if="hasActiveFilters()"
                class="flex items-end lg:col-span-4"
            >
                <Button variant="ghost" size="sm" @click="reset()">
                    <X class="size-4" />
                    Réinitialiser les filtres
                </Button>
            </div>
        </section>

        <div class="bg-card rounded-xl border">
            <Table v-if="calls.data.length > 0">
                <TableHeader>
                    <TableRow>
                        <TableHead>Date et heure</TableHead>
                        <TableHead>Client</TableHead>
                        <TableHead>Agent</TableHead>
                        <TableHead>Sens</TableHead>
                        <TableHead>Motif</TableHead>
                        <TableHead class="text-right">Durée</TableHead>
                        <TableHead>Statut</TableHead>
                        <TableHead>Étiquettes</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="call in calls.data"
                        :key="call.id"
                        class="focus-within:bg-muted/60 cursor-pointer"
                        @click="openCall(call)"
                    >
                        <TableCell class="whitespace-nowrap">
                            <Link
                                :href="show(call.id)"
                                class="focus-visible:ring-ring rounded focus-visible:ring-2 focus-visible:outline-none"
                                @click.stop
                            >
                                {{ formatDateTime(call.called_at) }}
                            </Link>
                        </TableCell>
                        <TableCell>
                            <span class="font-medium">{{
                                call.client?.full_name
                            }}</span>
                            <span
                                class="text-muted-foreground block text-xs tabular-nums"
                                >{{ call.client?.phone }}</span
                            >
                        </TableCell>
                        <TableCell>{{ call.agent?.name }}</TableCell>
                        <TableCell>
                            <StatusBadge :value="call.direction" />
                        </TableCell>
                        <TableCell>{{ call.reason.label }}</TableCell>
                        <TableCell class="text-right tabular-nums">
                            {{ formatDuration(call.duration_seconds) }}
                        </TableCell>
                        <TableCell>
                            <StatusBadge :value="call.status" />
                        </TableCell>
                        <TableCell>
                            <span class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="tag in call.tags"
                                    :key="tag.id"
                                    variant="secondary"
                                >
                                    {{ tag.name }}
                                </Badge>
                            </span>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <EmptyState
                v-else
                :icon="PhoneOff"
                title="Aucun appel sur cette sélection"
                :description="
                    hasActiveFilters()
                        ? 'Élargissez la période ou retirez un filtre.'
                        : 'Enregistrez le premier appel du plateau.'
                "
            >
                <Button as-child class="mt-2">
                    <Link :href="create()">
                        <Plus class="size-4" />
                        Enregistrer un appel
                    </Link>
                </Button>
            </EmptyState>
        </div>

        <Pagination
            :meta="calls.meta"
            :links="calls.links"
            label="appels"
        />
    </div>
</template>
