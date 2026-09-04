<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { UserSearch } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
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
import { index, show } from '@/routes/clients';
import type { Client, Paginated } from '@/types';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Clients', href: index() }] },
});

const props = defineProps<{
    clients: Paginated<Client>;
    filters: { search: string | null };
}>();

const { filters } = useFilters(
    { search: props.filters.search ?? '' },
    index().url,
    { debounced: ['search'] },
);
</script>

<template>
    <Head title="Clients" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Clients"
            description="Fiches en consultation, alimentées par le système de réservation. Cet outil suit les appels, il ne gère pas le fichier client."
        />

        <div class="grid max-w-md gap-1.5">
            <Label for="client-search">Recherche</Label>
            <Input
                id="client-search"
                v-model="filters.search"
                type="search"
                placeholder="Nom ou téléphone…"
            />
        </div>

        <div class="bg-card rounded-xl border">
            <Table v-if="clients.data.length > 0">
                <TableHeader>
                    <TableRow>
                        <TableHead>Client</TableHead>
                        <TableHead>Téléphone</TableHead>
                        <TableHead>E-mail</TableHead>
                        <TableHead class="text-right">Réservations</TableHead>
                        <TableHead class="text-right">Appels</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="client in clients.data" :key="client.id">
                        <TableCell class="font-medium">
                            <Link
                                :href="show(client.id)"
                                class="hover:underline"
                            >
                                {{ client.full_name }}
                            </Link>
                        </TableCell>
                        <TableCell class="tabular-nums">
                            {{ client.phone }}
                        </TableCell>
                        <TableCell class="text-muted-foreground">
                            {{ client.email ?? '—' }}
                        </TableCell>
                        <TableCell class="text-right tabular-nums">
                            {{ client.reservations_count }}
                        </TableCell>
                        <TableCell class="text-right tabular-nums">
                            {{ client.calls_count }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <EmptyState
                v-else
                :icon="UserSearch"
                title="Aucun client trouvé"
                description="Aucune fiche ne correspond à cette recherche."
            />
        </div>

        <Pagination :meta="clients.meta" label="clients" />
    </div>
</template>
