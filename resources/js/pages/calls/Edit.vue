<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import CallForm from '@/components/calls/CallForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { index, show, update } from '@/routes/calls';
import type { Call, CallFilterOptions, Client, Reservation } from '@/types';

const props = defineProps<{
    call: Call;
    clients: Client[];
    clientSearch: string;
    reservations: Reservation[];
    options: CallFilterOptions;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Appels', href: index() }],
    },
});
</script>

<template>
    <Head title="Modifier l'appel" />

    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
        <Heading
            title="Modifier l'appel"
            description="Corrigez la qualification, la durée ou les notes de cet appel."
        />

        <CallForm
            :call="props.call"
            :clients="clients"
            :client-search="clientSearch"
            :reservations="reservations"
            :options="options"
            :submit-url="update(props.call.id).url"
            method="put"
            submit-label="Enregistrer les modifications"
        >
            <template #secondary>
                <Button variant="ghost" as-child>
                    <Link :href="show(props.call.id)">
                        <ArrowLeft class="size-4" />
                        Annuler
                    </Link>
                </Button>
            </template>
        </CallForm>
    </div>
</template>
