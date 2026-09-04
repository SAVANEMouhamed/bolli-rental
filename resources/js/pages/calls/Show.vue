<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate, formatDateTime, formatDuration } from '@/lib/format';
import { destroy, edit, index } from '@/routes/calls';
import { show as showClient } from '@/routes/clients';
import { show as showReservation } from '@/routes/reservations';
import type { Call } from '@/types';

const props = defineProps<{
    call: Call;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Appels', href: index() }],
    },
});

const confirmingDelete = ref<boolean>(false);

const remove = (): void => {
    router.delete(destroy(props.call.id).url);
};
</script>

<template>
    <Head :title="`Appel du ${formatDateTime(call.called_at)}`" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <Heading
                :title="`Appel du ${formatDateTime(call.called_at)}`"
                :description="`${call.direction.label} · ${call.reason.label} · ${formatDuration(call.duration_seconds)}`"
            />
            <div class="flex gap-2">
                <Button v-if="call.can.update" variant="outline" as-child>
                    <Link :href="edit(call.id)">
                        <Pencil class="size-4" />
                        Modifier
                    </Link>
                </Button>
                <Button
                    v-if="call.can.delete"
                    variant="destructive"
                    @click="confirmingDelete = true"
                >
                    <Trash2 class="size-4" />
                    Supprimer
                </Button>
            </div>
        </div>

        <div
            v-if="confirmingDelete"
            class="border-destructive/40 bg-destructive/5 flex flex-wrap items-center justify-between gap-3 rounded-xl border p-4"
            role="alertdialog"
            aria-labelledby="delete-confirm"
        >
            <p id="delete-confirm" class="text-sm">
                Supprimer définitivement cet appel de l'historique du plateau ?
            </p>
            <div class="flex gap-2">
                <Button
                    variant="ghost"
                    size="sm"
                    @click="confirmingDelete = false"
                >
                    Annuler
                </Button>
                <Button variant="destructive" size="sm" @click="remove">
                    Confirmer la suppression
                </Button>
            </div>
        </div>

        <dl class="bg-card grid gap-4 rounded-xl border p-4 sm:grid-cols-2">
            <div>
                <dt class="text-muted-foreground text-sm">Client</dt>
                <dd class="font-medium">
                    <Link
                        v-if="call.client"
                        :href="showClient(call.client.id)"
                        class="hover:underline"
                    >
                        {{ call.client.full_name }}
                    </Link>
                    <span
                        v-if="call.client"
                        class="text-muted-foreground block text-sm tabular-nums"
                        >{{ call.client.phone }}</span
                    >
                </dd>
            </div>

            <div>
                <dt class="text-muted-foreground text-sm">Agent</dt>
                <dd class="font-medium">{{ call.agent?.name }}</dd>
            </div>

            <div>
                <dt class="text-muted-foreground text-sm">Statut</dt>
                <dd class="mt-1"><StatusBadge :value="call.status" /></dd>
            </div>

            <div>
                <dt class="text-muted-foreground text-sm">Sens</dt>
                <dd class="mt-1"><StatusBadge :value="call.direction" /></dd>
            </div>

            <div>
                <dt class="text-muted-foreground text-sm">Motif</dt>
                <dd class="font-medium">{{ call.reason.label }}</dd>
            </div>

            <div>
                <dt class="text-muted-foreground text-sm">Durée</dt>
                <dd class="font-medium tabular-nums">
                    {{ formatDuration(call.duration_seconds) }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-muted-foreground text-sm">
                    Réservation rattachée
                </dt>
                <dd class="font-medium">
                    <Link
                        v-if="call.reservation"
                        :href="showReservation(call.reservation.id)"
                        class="hover:underline"
                    >
                        {{ call.reservation.vehicle }} —
                        {{ formatDate(call.reservation.starts_at) }} →
                        {{ formatDate(call.reservation.ends_at) }}
                    </Link>
                    <span v-else class="text-muted-foreground font-normal">
                        Aucune
                    </span>
                </dd>
            </div>

            <div v-if="call.tags?.length" class="sm:col-span-2">
                <dt class="text-muted-foreground text-sm">Étiquettes</dt>
                <dd class="mt-1 flex flex-wrap gap-1">
                    <Badge
                        v-for="tag in call.tags"
                        :key="tag.id"
                        variant="secondary"
                    >
                        {{ tag.name }}
                    </Badge>
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-muted-foreground text-sm">Notes</dt>
                <dd class="mt-1 text-sm whitespace-pre-line">
                    {{ call.notes || '—' }}
                </dd>
            </div>
        </dl>

        <div>
            <Button variant="ghost" as-child>
                <Link :href="index()">
                    <ArrowLeft class="size-4" />
                    Retour à la liste des appels
                </Link>
            </Button>
        </div>
    </div>
</template>
