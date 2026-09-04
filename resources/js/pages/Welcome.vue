<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';

const page = usePage();
</script>

<template>
    <Head title="Accueil" />

    <div
        class="bg-background text-foreground flex min-h-screen flex-col items-center justify-center gap-8 p-6"
    >
        <div class="flex flex-col items-center gap-4 text-center">
            <div
                class="bg-sidebar-primary text-sidebar-primary-foreground flex size-12 items-center justify-center rounded-xl"
            >
                <AppLogoIcon class="size-7 fill-current" />
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ page.props.name }}
                </h1>
                <p class="text-muted-foreground max-w-md text-sm">
                    Outil interne de suivi des appels du service client.
                    Enregistrez, qualifiez et suivez chaque appel, et gardez une
                    vision d'ensemble de l'activité du plateau.
                </p>
            </div>
        </div>

        <Button as-child>
            <Link v-if="page.props.auth.user" :href="dashboard()">
                Accéder au tableau de bord
            </Link>
            <Link v-else :href="login()"> Se connecter </Link>
        </Button>

        <p class="text-muted-foreground text-xs">
            Accès réservé aux agents du service client.
        </p>
    </div>
</template>
