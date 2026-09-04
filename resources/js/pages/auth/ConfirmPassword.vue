<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';
import { dashboard } from '@/routes';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import PasskeyVerify from '@/components/PasskeyVerify.vue';

defineOptions({
    layout: {
        title: 'Confirmation du mot de passe',
        description:
            "Cette zone de l'application est protégée. Confirmez votre mot de passe pour continuer.",
    },
});

defineProps<{
    hasPasskeys: boolean;
}>();
</script>

<template>
    <Head title="Confirmation du mot de passe" />

    <PasskeyVerify
        v-if="hasPasskeys"
        :routes="{
            options: confirmOptions(),
            submit: confirmStore(),
        }"
        label="Confirmer avec une clé d'accès"
        loading-label="Confirmation…"
        separator="Ou confirmer avec le mot de passe"
    />

    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
    >
        <div class="space-y-6">
            <div class="grid gap-2">
                <Label for="password">Mot de passe</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center">
                <Button
                    class="w-full"
                    :disabled="processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="processing" />
                    Confirmer
                </Button>
            </div>
        </div>
    </Form>

    <div class="text-muted-foreground text-center text-sm">
        <TextLink
            :href="dashboard()"
            class="inline-flex items-center gap-1"
            data-test="back-to-dashboard-link"
        >
            <ArrowLeft class="size-4" />
            Retour au tableau de bord
        </TextLink>
    </div>
</template>
