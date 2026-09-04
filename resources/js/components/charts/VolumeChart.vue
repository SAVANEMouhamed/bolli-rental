<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js';
import { computed } from 'vue';
import AppChart from '@/components/charts/AppChart.vue';
import { chartChrome, chartPalette } from '@/lib/chart';
import { formatShortDate } from '@/lib/format';
import type { VolumeSeries } from '@/types';

const props = defineProps<{
    series: VolumeSeries;
    granularity: 'day' | 'week';
}>();

const labels = computed<string[]>(() =>
    props.series.labels.map((label) =>
        props.granularity === 'week'
            ? `sem. ${formatShortDate(label)}`
            : formatShortDate(label),
    ),
);

const data = computed<ChartData>(() => ({
    labels: labels.value,
    datasets: [
        {
            label: 'Appels',
            data: props.series.values,
            borderColor: chartPalette[0],
            backgroundColor: `${chartPalette[0]}22`,
            fill: true,
            tension: 0.3,
            pointRadius: props.series.labels.length > 45 ? 0 : 3,
        },
    ],
}));

const options = computed<ChartOptions>(() => {
    const chrome = chartChrome();

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: chrome.tick, maxRotation: 0, autoSkip: true },
            },
            y: {
                beginAtZero: true,
                grid: { color: chrome.grid },
                ticks: { color: chrome.tick, precision: 0 },
            },
        },
    };
});

const total = computed<number>(() =>
    props.series.values.reduce((sum, value) => sum + value, 0),
);
</script>

<template>
    <div>
        <AppChart
            type="line"
            :data="data"
            :options="options"
            :label="`Volume d'appels par ${granularity === 'week' ? 'semaine' : 'jour'}`"
        />

        <!-- Équivalent accessible : un lecteur d'écran ne lit pas un canvas. -->
        <table class="sr-only">
            <caption>
                Volume d'appels par
                {{ granularity === 'week' ? 'semaine' : 'jour' }} — {{ total }}
                au total
            </caption>
            <thead>
                <tr>
                    <th scope="col">Période</th>
                    <th scope="col">Appels</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(label, i) in series.labels" :key="label">
                    <td>{{ label }}</td>
                    <td>{{ series.values[i] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
