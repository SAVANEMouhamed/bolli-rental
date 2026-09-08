<?php

namespace App\Concerns;

/**
 * Expose un enum sous la forme attendue par les listes déroulantes et les filtres
 * du front. Les quatre enums du domaine en avaient besoin à l'identique.
 *
 * L'enum qui l'utilise doit exposer une méthode `label(): string`.
 */
trait EnumOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases(),
        );
    }
}
