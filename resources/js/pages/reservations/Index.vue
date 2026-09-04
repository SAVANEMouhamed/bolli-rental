<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CarFront } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import FilterSelect from '@/components/FilterSelect.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
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
import { formatDate } from '@/lib/format';
import { show as showClient } from '@/routes/clients';
import { index, show } from '@/routes/reservations';
import type { EnumValue, Paginated, Reservation } from '@/types';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Réservations', href: index() }] },
});

const props = defineProps<{
    reservations: Paginated<Reservation>;
    filters: { status: string | null };
    options: { statuses: EnumValue[] };
}>();

const { filters } = useFilters(
    { status: props.filters.status ?? null },
    index().url,
);
</script>

<template>
    <Head title="Réservations" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Réservations"
            description="Locations en cours, terminées et annulées, en consultation : elles servent de contexte aux appels et viennent du système de réservation."
        />

        <div class="grid max-w-xs gap-1.5">
            <Label for="reservation-status">Statut</Label>
            <FilterSelect
                id="reservation-status"
                v-model="filters.status"
                :options="options.statuses"
                placeholder="Tous les statuts"
            />
        </div>

        <div class="bg-card rounded-xl border">
            <Table v-if="reservations.data.length > 0">
                <TableHeader>
                    <TableRow>
                        <TableHead>Véhicule</TableHead>
                        <TableHead>Client</TableHead>
                        <TableHead>Période</TableHead>
                        <TableHead>Statut</TableHead>
                        <TableHead class="text-right">Appels</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="reservation in reservations.data"
                        :key="reservation.id"
                    >
                        <TableCell class="font-medium">
                            <Link
                                :href="show(reservation.id)"
                                class="hover:underline"
                            >
                                {{ reservation.vehicle }}
                            </Link>
                        </TableCell>
                        <TableCell>
                            <Link
                                v-if="reservation.client"
                                :href="showClient(reservation.client.id)"
                                class="hover:underline"
                            >
                                {{ reservation.client.full_name }}
                            </Link>
                        </TableCell>
                        <TableCell class="whitespace-nowrap">
                            {{ formatDate(reservation.starts_at) }} →
                            {{ formatDate(reservation.ends_at) }}
                        </TableCell>
                        <TableCell>
                            <StatusBadge :value="reservation.status" />
                        </TableCell>
                        <TableCell class="text-right tabular-nums">
                            {{ reservation.calls_count }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <EmptyState
                v-else
                :icon="CarFront"
                title="Aucune réservation"
                description="Aucune location ne correspond à ce statut."
            />
        </div>

        <Pagination
            :meta="reservations.meta"
            :links="reservations.links"
            label="réservations"
        />
    </div>
</template>
