import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

type FilterValues = Record<string, string | number | null | undefined>;

type Options = {
    /** Champs dont la saisie est continue et mérite un anti-rebond. */
    debounced?: string[];
    debounceMs?: number;
};

/**
 * Synchronise un jeu de filtres avec l'URL. L'état vit dans l'URL et non dans le
 * composant : la page reste rechargeable, partageable entre agents, et les
 * filtres restent testables côté serveur.
 */
export function useFilters<T extends FilterValues>(
    initial: T,
    url: string,
    options: Options = {},
) {
    const { debounced = [], debounceMs = 300 } = options;

    const filters = reactive({ ...initial }) as T;

    let timer: ReturnType<typeof setTimeout> | undefined;

    const visit = (): void => {
        // Les valeurs vides ne partent pas dans l'URL : elle reste lisible et le
        // contrôleur n'a pas à distinguer « absent » de « chaîne vide ».
        const query = Object.fromEntries(
            Object.entries(filters).filter(
                ([, value]) =>
                    value !== null && value !== undefined && value !== '',
            ),
        );

        router.get(url, query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    watch(
        () => ({ ...filters }),
        (next, previous) => {
            const changed = Object.keys(next).filter(
                (key) => next[key] !== previous[key],
            );

            if (changed.length === 0) {
                return;
            }

            clearTimeout(timer);

            const wait = changed.some((key) => debounced.includes(key))
                ? debounceMs
                : 0;

            timer = setTimeout(visit, wait);
        },
    );

    const reset = (): void => {
        for (const key of Object.keys(filters)) {
            (filters as FilterValues)[key] = null;
        }
    };

    const hasActiveFilters = (): boolean =>
        Object.values(filters).some(
            (value) => value !== null && value !== undefined && value !== '',
        );

    return { filters, reset, hasActiveFilters };
}
