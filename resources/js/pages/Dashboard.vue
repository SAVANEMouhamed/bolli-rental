<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, PhoneCall, ShieldAlert, TrendingUp } from '@lucide/vue';
import { computed } from 'vue';
import DistributionChart from '@/components/charts/DistributionChart.vue';
import VolumeChart from '@/components/charts/VolumeChart.vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterSelect from '@/components/FilterSelect.vue';
import Heading from '@/components/Heading.vue';
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
import { formatDuration, formatPercent } from '@/lib/format';
import { dashboard } from '@/routes';
import { index as callsIndex } from '@/routes/calls';
import type {
    AgentRanking,
    DashboardFilters,
    DashboardSummary,
    Distribution,
    VolumeSeries,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Tableau de bord', href: dashboard() }],
    },
});

const props = defineProps<{
    filters: DashboardFilters;
    summary: DashboardSummary;
    volume: VolumeSeries;
    byReason: Distribution[];
    byStatus: Distribution[];
    agentRanking: AgentRanking[];
}>();

const { filters } = useFilters({ ...props.filters }, dashboard().url);

const granularityOptions = [
    { value: 'day', label: 'Par jour' },
    { value: 'week', label: 'Par semaine' },
];

const stats = computed(() => [
    {
        label: "Volume d'appels",
        value: props.summary.total.toLocaleString('fr-FR'),
        icon: PhoneCall,
    },
    {
        label: 'Durée moyenne',
        value: formatDuration(props.summary.average_duration),
        icon: Clock,
    },
    {
        label: 'Taux de résolution',
        value: formatPercent(props.summary.resolved, props.summary.total),
        icon: TrendingUp,
    },
    {
        label: 'Appels escaladés',
        value: props.summary.escalated.toLocaleString('fr-FR'),
        icon: ShieldAlert,
    },
]);
</script>

<template>
    <Head title="Tableau de bord" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Tableau de bord"
            description="Activité du service client sur la période sélectionnée."
        />

        <section
            class="bg-card grid gap-3 rounded-xl border p-4 sm:grid-cols-3"
            aria-label="Période analysée"
        >
            <div class="grid gap-1.5">
                <Label for="dashboard-from">Du</Label>
                <Input id="dashboard-from" v-model="filters.from" type="date" />
            </div>
            <div class="grid gap-1.5">
                <Label for="dashboard-to">Au</Label>
                <Input id="dashboard-to" v-model="filters.to" type="date" />
            </div>
            <div class="grid gap-1.5">
                <Label for="dashboard-granularity">Granularité</Label>
                <FilterSelect
                    id="dashboard-granularity"
                    v-model="filters.granularity"
                    :options="granularityOptions"
                    :clearable="false"
                />
            </div>
        </section>

        <section
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
            aria-label="Indicateurs clés"
        >
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="bg-card rounded-xl border p-4"
            >
                <div
                    class="text-muted-foreground flex items-center gap-2 text-sm"
                >
                    <component
                        :is="stat.icon"
                        class="size-4"
                        aria-hidden="true"
                    />
                    {{ stat.label }}
                </div>
                <p class="mt-2 text-2xl font-semibold tabular-nums">
                    {{ stat.value }}
                </p>
            </div>
        </section>

        <template v-if="summary.total > 0">
            <section class="bg-card rounded-xl border p-4">
                <h2 class="mb-4 font-medium">
                    Volume d'appels
                    {{
                        filters.granularity === 'week'
                            ? 'par semaine'
                            : 'par jour'
                    }}
                </h2>
                <VolumeChart
                    :series="volume"
                    :granularity="filters.granularity"
                />
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="bg-card rounded-xl border p-4">
                    <h2 class="mb-4 font-medium">Répartition par motif</h2>
                    <DistributionChart
                        :distribution="byReason"
                        title="Répartition des appels par motif"
                    />
                </div>
                <div class="bg-card rounded-xl border p-4">
                    <h2 class="mb-4 font-medium">Répartition par statut</h2>
                    <DistributionChart
                        :distribution="byStatus"
                        title="Répartition des appels par statut"
                    />
                </div>
            </section>

            <section class="bg-card rounded-xl border">
                <h2 class="px-4 pt-4 font-medium">
                    Classement des agents par volume traité
                </h2>
                <Table class="mt-2">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Agent</TableHead>
                            <TableHead class="text-right">Appels</TableHead>
                            <TableHead class="text-right">Part</TableHead>
                            <TableHead class="text-right">
                                Durée moyenne
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="agent in agentRanking" :key="agent.id">
                            <TableCell class="font-medium">
                                {{ agent.name }}
                            </TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ agent.total }}
                            </TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ formatPercent(agent.total, summary.total) }}
                            </TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ formatDuration(agent.average_duration) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </section>
        </template>

        <EmptyState
            v-else
            :icon="PhoneCall"
            title="Aucun appel sur cette période"
            description="Élargissez la période analysée ou enregistrez un premier appel."
            class="bg-card rounded-xl border"
        >
            <Button as-child class="mt-2">
                <Link :href="callsIndex()">Voir les appels</Link>
            </Button>
        </EmptyState>
    </div>
</template>
