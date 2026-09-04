/**
 * Reflète exactement ce que les contrôleurs envoient. Quand un contrôleur change
 * de forme, ce fichier change dans le même commit.
 */

export type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
};

/** Couple valeur/libellé produit par les enums PHP pour alimenter les filtres. */
export type Option = {
    value: string;
    label: string;
};

export type Agent = {
    id: number;
    name: string;
    email: string;
    calls_count: number;
    is_current: boolean;
};
