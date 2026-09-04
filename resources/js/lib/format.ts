const dateTimeFormatter = new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'short',
    timeStyle: 'short',
});

const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });

const shortDateFormatter = new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
});

/**
 * Une durée d'appel se lit « 4 min 12 s », jamais « 252 ». Au-delà de l'heure,
 * on bascule sur « 1 h 05 » : un appel du service client qui dure une heure est
 * une information en soi.
 */
export function formatDuration(seconds: number): string {
    if (seconds < 60) {
        return `${seconds} s`;
    }

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const rest = seconds % 60;

    if (hours > 0) {
        return `${hours} h ${String(minutes).padStart(2, '0')}`;
    }

    return rest === 0 ? `${minutes} min` : `${minutes} min ${rest} s`;
}

export function formatDateTime(iso: string): string {
    return dateTimeFormatter.format(new Date(iso));
}

export function formatDate(iso: string): string {
    return dateFormatter.format(new Date(iso));
}

export function formatShortDate(iso: string): string {
    return shortDateFormatter.format(new Date(iso));
}

/** Valeur d'un `<input type="datetime-local">` à partir d'un instant ISO. */
export function toDateTimeLocal(iso: string): string {
    const date = new Date(iso);
    const offset = date.getTimezoneOffset() * 60_000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

export function formatPercent(part: number, whole: number): string {
    if (whole === 0) {
        return '—';
    }

    return `${Math.round((part / whole) * 100)} %`;
}
