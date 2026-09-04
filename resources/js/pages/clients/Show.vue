<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, PhoneOff } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate, formatDateTime, formatDuration } from '@/lib/format';
import { show as showCall } from '@/routes/calls';
import { index } from '@/routes/clients';
import { show as showReservation } from '@/routes/reservations';
import type { Call, Client, Paginated } from '@/types';

defineProps<{
    client: Client;
    calls: Paginated<Call>;
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Clients', href: index() }] },
});
</script>

<template>
    <Head :title="client.full_name" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            :title="client.full_name"
            :description="`${client.phone}${client.email ? ' · ' + client.email : ''}`"
        />

        <section class="space-y-3">
            <h2 class="text-lg font-medium">Réservations</h2>
            <div class="bg-card rounded-xl border">
                <Table v-if="client.reservations?.length">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Véhicule</TableHead>
                            <TableHead>Période</TableHead>
                            <TableHead>Statut</TableHead>
                            <TableHead class="text-right">Appels</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="reservation in client.reservations"
                            :key="reservation.id"
                        >
                            <TableCell class="font-medium">
                                <Link
                                    :href="showReservation(reservation.id)"
                                    class="hover:underline"
                                >
                                    {{ reservation.vehicle }}
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
                    title="Aucune réservation"
                    description="Ce client n'a encore aucune location enregistrée."
                />
            </div>
        </section>

        <section class="space-y-3">
            <h2 class="text-lg font-medium">Appels</h2>
            <div class="bg-card rounded-xl border">
                <Table v-if="calls.data.length > 0">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Date et heure</TableHead>
                            <TableHead>Agent</TableHead>
                            <TableHead>Motif</TableHead>
                            <TableHead class="text-right">Durée</TableHead>
                            <TableHead>Statut</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="call in calls.data" :key="call.id">
                            <TableCell class="whitespace-nowrap">
                                <Link
                                    :href="showCall(call.id)"
                                    class="hover:underline"
                                >
                                    {{ formatDateTime(call.called_at) }}
                                </Link>
                            </TableCell>
                            <TableCell>{{ call.agent?.name }}</TableCell>
                            <TableCell>{{ call.reason.label }}</TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ formatDuration(call.duration_seconds) }}
                            </TableCell>
                            <TableCell>
                                <StatusBadge :value="call.status" />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <EmptyState
                    v-else
                    :icon="PhoneOff"
                    title="Aucun appel"
                    description="Ce client n'a jamais contacté le service client."
                />
            </div>
            <Pagination
                :meta="calls.meta"
                :links="calls.links"
                label="appels"
            />
        </section>

        <div>
            <Button variant="ghost" as-child>
                <Link :href="index()">
                    <ArrowLeft class="size-4" />
                    Retour aux clients
                </Link>
            </Button>
        </div>
    </div>
</template>
