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
import { show as showClient } from '@/routes/clients';
import { index } from '@/routes/reservations';
import type { Call, Paginated, Reservation } from '@/types';

defineProps<{
    reservation: Reservation;
    calls: Paginated<Call>;
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Réservations', href: index() }] },
});
</script>

<template>
    <Head :title="reservation.vehicle" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            :title="reservation.vehicle"
            :description="`${formatDate(reservation.starts_at)} → ${formatDate(reservation.ends_at)}`"
        />

        <dl class="bg-card grid gap-4 rounded-xl border p-4 sm:grid-cols-3">
            <div>
                <dt class="text-muted-foreground text-sm">Client</dt>
                <dd class="font-medium">
                    <Link
                        v-if="reservation.client"
                        :href="showClient(reservation.client.id)"
                        class="hover:underline"
                    >
                        {{ reservation.client.full_name }}
                    </Link>
                </dd>
            </div>
            <div>
                <dt class="text-muted-foreground text-sm">Statut</dt>
                <dd class="mt-1">
                    <StatusBadge :value="reservation.status" />
                </dd>
            </div>
            <div>
                <dt class="text-muted-foreground text-sm">Téléphone</dt>
                <dd class="font-medium tabular-nums">
                    {{ reservation.client?.phone }}
                </dd>
            </div>
        </dl>

        <section class="space-y-3">
            <h2 class="text-lg font-medium">Appels rattachés</h2>
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
                    title="Aucun appel rattaché"
                    description="Aucun appel du service client ne concerne cette location."
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
                    Retour aux réservations
                </Link>
            </Button>
        </div>
    </div>
</template>
