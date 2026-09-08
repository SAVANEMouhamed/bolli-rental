<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import { computed } from 'vue';
import AppChart from '@/components/charts/AppChart.vue';
import { chartChrome, chartPalette } from '@/lib/chart';
import { formatPercent } from '@/lib/format';
import type { Distribution } from '@/types';

const props = defineProps<{
    distribution: Distribution[];
    title: string;
}>();

const total = computed<number>(() =>
    props.distribution.reduce((sum, slice) => sum + slice.total, 0),
);

const data = computed<ChartData>(() => ({
    labels: props.distribution.map((slice) => slice.label),
    datasets: [
        {
            data: props.distribution.map((slice) => slice.total),
            backgroundColor: props.distribution.map(
                (_, i) => chartPalette[i % chartPalette.length],
            ),
            borderWidth: 0,
        },
    ],
}));

const options = computed<ChartOptions>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '58%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: { color: chartChrome().tick, boxWidth: 12, padding: 12 },
        },
    },
}));
</script>

<template>
    <div>
        <AppChart
            type="doughnut"
            :data="data"
            :options="options"
            :label="title"
            :height="240"
        />

        <table class="sr-only">
            <caption>
                {{
                    title
                }}
            </caption>
            <thead>
                <tr>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Appels</th>
                    <th scope="col">Part</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="slice in distribution" :key="slice.value">
                    <td>{{ slice.label }}</td>
                    <td>{{ slice.total }}</td>
                    <td>{{ formatPercent(slice.total, total) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
