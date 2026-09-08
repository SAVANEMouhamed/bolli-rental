<script setup lang="ts">
import type { ChartConfiguration, ChartData, ChartOptions } from 'chart.js';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart } from '@/lib/chart';
import { useAppearance } from '@/composables/useAppearance';

type ChartKind = 'line' | 'bar' | 'doughnut';

const props = defineProps<{
    type: ChartKind;
    data: ChartData;
    options: ChartOptions;
    label: string;
    height?: number;
}>();

const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const { resolvedAppearance } = useAppearance();

const render = (): void => {
    if (canvas.value === null) {
        return;
    }

    chart?.destroy();
    chart = new Chart(canvas.value, {
        type: props.type,
        data: props.data,
        options: props.options,
    } as ChartConfiguration);
};

onMounted(render);

// Les couleurs de grille sont calculées à partir du thème appliqué : un
// changement de thème impose de reconstruire le graphique, pas seulement de le
// mettre à jour.
watch(() => [props.data, props.options, resolvedAppearance.value], render, {
    deep: true,
});

onBeforeUnmount(() => {
    chart?.destroy();
    chart = null;
});
</script>

<template>
    <div class="relative w-full" :style="{ height: `${height ?? 260}px` }">
        <canvas ref="canvas" role="img" :aria-label="label" />
    </div>
</template>
