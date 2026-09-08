/**
 * Reflète exactement ce que les contrôleurs envoient. Quand un contrôleur change
 * de forme, ce fichier change dans le même commit.
 */

/**
 * Un lien de la barre de pagination. `url` est nul sur l'ellipse et sur
 * précédent/suivant quand on est au bord ; `page` est nul dans les mêmes cas.
 */
export type PaginationLink = {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
};

/**
 * Forme exacte d'une `ResourceCollection` paginée de Laravel : `links` porte les
 * quatre raccourcis premier/dernier/précédent/suivant, et c'est `meta.links` qui
 * contient la barre de pagination numérotée.
 */
export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
        links: PaginationLink[];
    };
};

/** Couple produit par les enums PHP : la valeur pilote, le libellé s'affiche. */
export type EnumValue = {
    value: string;
    label: string;
};

export type Agent = {
    id: number;
    name: string;
};

export type AgentAccount = Agent & {
    email: string;
    calls_count: number;
    is_current: boolean;
};

export type Tag = {
    id: number;
    name: string;
    slug: string;
};

export type Client = {
    id: number;
    full_name: string;
    phone: string;
    email: string | null;
    calls_count?: number;
    reservations_count?: number;
    reservations?: Reservation[];
};

export type Reservation = {
    id: number;
    vehicle: string;
    starts_at: string;
    ends_at: string;
    status: EnumValue;
    client?: Client | null;
    calls_count?: number;
};

export type Call = {
    id: number;
    direction: EnumValue;
    reason: EnumValue;
    status: EnumValue;
    called_at: string;
    duration_seconds: number;
    notes: string | null;
    client?: Client;
    agent?: Agent;
    reservation?: Reservation | null;
    tags?: Tag[];
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type CallFilters = {
    search?: string | null;
    agent?: number | string | null;
    client?: number | string | null;
    status?: string | null;
    reason?: string | null;
    direction?: string | null;
    tag?: string | null;
    from?: string | null;
    to?: string | null;
};

export type CallFilterOptions = {
    directions: EnumValue[];
    reasons: EnumValue[];
    statuses: EnumValue[];
    agents: Agent[];
    tags: Tag[];
};

export type DashboardFilters = {
    from: string;
    to: string;
    granularity: 'day' | 'week';
};

export type DashboardSummary = {
    total: number;
    average_duration: number;
    total_duration: number;
    resolved: number;
    escalated: number;
};

export type VolumeSeries = {
    labels: string[];
    values: number[];
};

export type Distribution = EnumValue & {
    total: number;
};

export type AgentRanking = {
    id: number;
    name: string;
    total: number;
    average_duration: number;
};
