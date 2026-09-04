<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import FilterSelect from '@/components/FilterSelect.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, toDateTimeLocal } from '@/lib/format';
import type { Call, CallFilterOptions, Client, Reservation } from '@/types';

const props = defineProps<{
    call?: Call;
    clients: Client[];
    clientSearch: string;
    reservations: Reservation[];
    options: CallFilterOptions;
    submitUrl: string;
    method: 'post' | 'put';
    submitLabel: string;
}>();

const form = useForm({
    client_id: props.call?.client?.id ?? null,
    reservation_id: props.call?.reservation?.id ?? null,
    direction: props.call?.direction.value ?? 'inbound',
    reason: props.call?.reason.value ?? 'reservation',
    status: props.call?.status.value ?? 'resolved',
    // Défaut intelligent : un agent enregistre presque toujours l'appel qu'il
    // vient de raccrocher.
    called_at: toDateTimeLocal(
        props.call?.called_at ?? new Date().toISOString(),
    ),
    duration_seconds: props.call?.duration_seconds ?? 0,
    notes: props.call?.notes ?? '',
    tags: props.call?.tags?.map((tag) => tag.id) ?? [],
});

const search = ref<string>(props.clientSearch);
const minutes = ref<number>(Math.floor(form.duration_seconds / 60));
const seconds = ref<number>(form.duration_seconds % 60);

let searchTimer: ReturnType<typeof setTimeout> | undefined;

// Recherche asynchrone du client par visite partielle Inertia : pas de `<select>`
// de plusieurs centaines d'entrées, et pas d'endpoint JSON parallèle non plus.
watch(search, (term) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.reload({
            only: ['clients'],
            data: { client_search: term, client_id: form.client_id ?? '' },
        });
    }, 300);
});

// Changer de client invalide la réservation déjà choisie : elle appartiendrait à
// quelqu'un d'autre, ce que la Form Request rejetterait de toute façon.
watch(
    () => form.client_id,
    (clientId) => {
        form.reservation_id = null;
        router.reload({
            only: ['reservations'],
            data: { client_id: clientId ?? '', client_search: search.value },
        });
    },
);

watch([minutes, seconds], ([m, s]) => {
    form.duration_seconds = Math.max(0, (m || 0) * 60 + (s || 0));
});

const toggleTag = (id: number): void => {
    form.tags = form.tags.includes(id)
        ? form.tags.filter((tag) => tag !== id)
        : [...form.tags, id];
};

const submit = (): void => {
    form.submit(props.method, props.submitUrl, { preserveScroll: true });
};
</script>

<template>
    <form class="grid gap-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="client_search">Client concerné</Label>
            <div class="relative">
                <Search
                    class="text-muted-foreground pointer-events-none absolute top-2.5 left-2.5 size-4"
                    aria-hidden="true"
                />
                <Input
                    id="client_search"
                    v-model="search"
                    type="search"
                    class="pl-8"
                    placeholder="Rechercher par nom ou téléphone…"
                />
            </div>
            <FilterSelect
                id="client_id"
                v-model="form.client_id"
                :options="
                    clients.map((client) => ({
                        value: client.id,
                        label: `${client.full_name} — ${client.phone}`,
                    }))
                "
                placeholder="Sélectionner un client"
                aria-label="Client concerné"
            />
            <InputError :message="form.errors.client_id" />
        </div>

        <div class="grid gap-2">
            <Label for="reservation_id">Réservation (facultatif)</Label>
            <FilterSelect
                id="reservation_id"
                v-model="form.reservation_id"
                :options="
                    reservations.map((reservation) => ({
                        value: reservation.id,
                        label: `${reservation.vehicle} — ${formatDate(reservation.starts_at)} → ${formatDate(reservation.ends_at)} (${reservation.status.label})`,
                    }))
                "
                :placeholder="
                    form.client_id === null
                        ? 'Choisissez d\'abord un client'
                        : 'Aucune réservation rattachée'
                "
            />
            <InputError :message="form.errors.reservation_id" />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="grid gap-2">
                <Label for="direction">Sens</Label>
                <FilterSelect
                    id="direction"
                    v-model="form.direction"
                    :options="options.directions"
                    placeholder="—"
                />
                <InputError :message="form.errors.direction" />
            </div>
            <div class="grid gap-2">
                <Label for="reason">Motif</Label>
                <FilterSelect
                    id="reason"
                    v-model="form.reason"
                    :options="options.reasons"
                    placeholder="—"
                />
                <InputError :message="form.errors.reason" />
            </div>
            <div class="grid gap-2">
                <Label for="status">Statut</Label>
                <FilterSelect
                    id="status"
                    v-model="form.status"
                    :options="options.statuses"
                    placeholder="—"
                />
                <InputError :message="form.errors.status" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="called_at">Date et heure de l'appel</Label>
                <Input
                    id="called_at"
                    v-model="form.called_at"
                    type="datetime-local"
                    required
                />
                <InputError :message="form.errors.called_at" />
            </div>

            <fieldset class="grid gap-2">
                <legend class="mb-2 text-sm font-medium">Durée</legend>
                <div class="flex items-center gap-2">
                    <Input
                        id="duration_minutes"
                        v-model.number="minutes"
                        type="number"
                        min="0"
                        max="1440"
                        class="w-20"
                        aria-label="Durée en minutes"
                    />
                    <span class="text-muted-foreground text-sm">min</span>
                    <Input
                        id="duration_seconds_part"
                        v-model.number="seconds"
                        type="number"
                        min="0"
                        max="59"
                        class="w-20"
                        aria-label="Durée, secondes"
                    />
                    <span class="text-muted-foreground text-sm">s</span>
                </div>
                <InputError :message="form.errors.duration_seconds" />
            </fieldset>
        </div>

        <fieldset class="grid gap-2">
            <legend class="mb-2 text-sm font-medium">Étiquettes</legend>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <Label
                    v-for="tag in options.tags"
                    :key="tag.id"
                    class="flex items-center gap-2 font-normal"
                >
                    <Checkbox
                        :model-value="form.tags.includes(tag.id)"
                        @update:model-value="toggleTag(tag.id)"
                    />
                    <span>{{ tag.name }}</span>
                </Label>
            </div>
            <InputError :message="form.errors.tags" />
        </fieldset>

        <div class="grid gap-2">
            <Label for="notes">Notes</Label>
            <textarea
                id="notes"
                v-model="form.notes"
                rows="5"
                maxlength="5000"
                class="border-input bg-background focus-visible:border-ring focus-visible:ring-ring/50 rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                placeholder="Ce que le client a demandé, ce qui a été fait, ce qui reste à faire."
            />
            <InputError :message="form.errors.notes" />
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                {{ submitLabel }}
            </Button>
            <slot name="secondary" />
        </div>
    </form>
</template>
