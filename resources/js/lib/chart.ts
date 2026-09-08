import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

/**
 * Enregistrement explicite des seuls modules utilisés — trois types de graphiques,
 * deux échelles, deux greffons. `registerables` embarquerait tout Chart.js dans le
 * bundle, y compris les types radar, bulle et polaire dont l'outil ne se sert pas.
 */
Chart.register(
    LineController,
    BarController,
    DoughnutController,
    LineElement,
    PointElement,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
);

/**
 * Palette catégorielle fixe : les variables de thème du starter kit sont
 * monochromes et ne distinguent pas des séries. Ces teintes gardent un contraste
 * suffisant sur fond clair comme sur fond sombre.
 */
export const chartPalette = [
    '#2563eb',
    '#0d9488',
    '#d97706',
    '#dc2626',
    '#7c3aed',
    '#0891b2',
];

/** Couleurs de grille et de graduation lues sur le thème effectivement appliqué. */
export function chartChrome(): { grid: string; tick: string } {
    const styles = getComputedStyle(document.documentElement);

    return {
        grid: styles.getPropertyValue('--border').trim() || '#e5e5e5',
        tick: styles.getPropertyValue('--muted-foreground').trim() || '#737373',
    };
}

export { Chart };
