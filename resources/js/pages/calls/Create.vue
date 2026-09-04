<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import CallForm from '@/components/calls/CallForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index, store } from '@/routes/calls';
import type { CallFilterOptions, Client, Reservation } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Appels', href: index() },
            { title: 'Nouvel appel', href: create() },
        ],
    },
});

defineProps<{
    clients: Client[];
    clientSearch: string;
    reservations: Reservation[];
    options: CallFilterOptions;
}>();
</script>

<template>
    <Head title="Enregistrer un appel" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
        <Heading
            title="Enregistrer un appel"
            description="L'appel est crédité à votre compte agent."
        />

        <CallForm
            :clients="clients"
            :client-search="clientSearch"
            :reservations="reservations"
            :options="options"
            :submit-url="store().url"
            method="post"
            submit-label="Enregistrer l'appel"
        >
            <template #secondary>
                <Button variant="ghost" as-child>
                    <Link :href="index()">
                        <ArrowLeft class="size-4" />
                        Annuler
                    </Link>
                </Button>
            </template>
        </CallForm>
    </div>
</template>
