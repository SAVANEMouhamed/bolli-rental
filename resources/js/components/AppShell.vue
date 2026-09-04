<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { SidebarProvider } from '@/components/ui/sidebar';
import type { AppVariant } from '@/types';

type Props = {
    variant?: AppVariant;
};

withDefaults(defineProps<Props>(), {
    variant: 'sidebar',
});

const isOpen = usePage().props.sidebarOpen;
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <!--
        `h-svh overflow-hidden` : le document lui-même ne défile plus, c'est la zone
        de contenu qui défile. La barre latérale et l'en-tête restent donc à l'écran
        quelle que soit la longueur d'une liste.
    -->
    <SidebarProvider
        v-else
        :default-open="isOpen"
        class="h-svh overflow-hidden"
    >
        <slot />
    </SidebarProvider>
</template>
