<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon?: Component;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-2 px-6 py-12">
        <component
            :is="icon"
            v-if="icon"
            class="text-muted-foreground size-8"
            aria-hidden="true"
        />
        <p class="font-medium">{{ title }}</p>
        <p v-if="description" class="text-muted-foreground text-sm">
            {{ description }}
        </p>
        <slot />
    </div>
</template>
