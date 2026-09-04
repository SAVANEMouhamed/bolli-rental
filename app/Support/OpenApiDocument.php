<?php

namespace App\Support;

use App\Enums\CallDirection;
use App\Enums\CallReason;
use App\Enums\CallStatus;
use App\Enums\ReservationStatus;

/**
 * Document OpenAPI 3.1 de l'API interne.
 *
 * Écrit en PHP plutôt qu'en YAML statique pour une raison précise : les valeurs
 * qui dérivent le plus vite — motifs, statuts, sens d'appel — sont lues sur les
 * enums de l'application. Ajouter un motif met la documentation à jour sans que
 * personne n'y pense, et un test vérifie cette correspondance.
 *
 * Un générateur complet (dedoc/scramble) resterait préférable ; il demande une
 * dépendance supplémentaire, soumise à validation.
 */
class OpenApiDocument
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => config('app.name').' — API interne',
                'version' => '1.0.0',
                'description' => <<<'MD'
                API REST en lecture seule exposant le suivi des appels du service
                client, ses clients, ses réservations et ses statistiques.

                **Authentification.** Les requêtes sont authentifiées par le cookie
                de session de l'application : connectez-vous d'abord sur `/login`,
                puis appelez ces endpoints depuis le même navigateur. « Try it out »
                fonctionne directement depuis cette page.

                **Écriture.** Volontairement absente : ouvrir l'écriture à un client
                mobile suppose une authentification par jeton (Laravel Sanctum), qui
                n'est pas encore installée. La lecture couvre le besoin annoncé,
                « exposer les appels ».
                MD,
            ],
            'servers' => [
                ['url' => config('app.url'), 'description' => 'Instance courante'],
            ],
            'tags' => [
                ['name' => 'Appels', 'description' => 'Suivi des appels du service client'],
                ['name' => 'Clients', 'description' => 'Fiches clients'],
                ['name' => 'Réservations', 'description' => 'Locations de véhicules'],
                ['name' => 'Statistiques', 'description' => 'Agrégations du tableau de bord'],
            ],
            'paths' => $this->paths(),
            'components' => [
                'schemas' => $this->schemas(),
                'responses' => $this->responses(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paths(): array
    {
        return [
            '/api/v1/calls' => [
                'get' => [
                    'tags' => ['Appels'],
                    'summary' => 'Lister les appels',
                    'description' => 'Du plus récent au plus ancien. Accepte les mêmes filtres que l\'écran web.',
                    'parameters' => [
                        ...$this->callFilterParameters(),
                        $this->perPageParameter(),
                        $this->pageParameter(),
                    ],
                    'responses' => [
                        '200' => $this->paginatedResponse('Call', 'Page d\'appels'),
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],
            '/api/v1/calls/{call}' => [
                'get' => [
                    'tags' => ['Appels'],
                    'summary' => 'Consulter un appel',
                    'parameters' => [$this->idParameter('call', 'Identifiant de l\'appel')],
                    'responses' => [
                        '200' => $this->singleResponse('Call', 'L\'appel demandé'),
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                    ],
                ],
            ],
            '/api/v1/clients' => [
                'get' => [
                    'tags' => ['Clients'],
                    'summary' => 'Lister les clients',
                    'parameters' => [
                        $this->queryParameter('search', 'string', 'Recherche sur le nom ou le téléphone'),
                        $this->perPageParameter(),
                        $this->pageParameter(),
                    ],
                    'responses' => [
                        '200' => $this->paginatedResponse('Client', 'Page de clients'),
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    ],
                ],
            ],
            '/api/v1/clients/{client}' => [
                'get' => [
                    'tags' => ['Clients'],
                    'summary' => 'Consulter un client et ses réservations',
                    'parameters' => [$this->idParameter('client', 'Identifiant du client')],
                    'responses' => [
                        '200' => $this->singleResponse('Client', 'Le client demandé'),
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                    ],
                ],
            ],
            '/api/v1/reservations' => [
                'get' => [
                    'tags' => ['Réservations'],
                    'summary' => 'Lister les réservations',
                    'parameters' => [
                        $this->queryParameter('status', 'string', 'Statut de la location', ReservationStatus::class),
                        $this->perPageParameter(),
                        $this->pageParameter(),
                    ],
                    'responses' => [
                        '200' => $this->paginatedResponse('Reservation', 'Page de réservations'),
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],
            '/api/v1/reservations/{reservation}' => [
                'get' => [
                    'tags' => ['Réservations'],
                    'summary' => 'Consulter une réservation',
                    'parameters' => [$this->idParameter('reservation', 'Identifiant de la réservation')],
                    'responses' => [
                        '200' => $this->singleResponse('Reservation', 'La réservation demandée'),
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                        '404' => ['$ref' => '#/components/responses/NotFound'],
                    ],
                ],
            ],
            '/api/v1/statistics' => [
                'get' => [
                    'tags' => ['Statistiques'],
                    'summary' => 'Statistiques d\'appels sur une période',
                    'description' => 'Mêmes agrégations que le tableau de bord web. Période par défaut : 30 jours.',
                    'parameters' => [
                        $this->queryParameter('from', 'string', 'Début de période (AAAA-MM-JJ)', format: 'date'),
                        $this->queryParameter('to', 'string', 'Fin de période (AAAA-MM-JJ)', format: 'date'),
                        [
                            'name' => 'granularity',
                            'in' => 'query',
                            'description' => 'Regroupement de la série temporelle',
                            'schema' => ['type' => 'string', 'enum' => ['day', 'week'], 'default' => 'day'],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Statistiques de la période',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => ['data' => ['$ref' => '#/components/schemas/Statistics']],
                                    ],
                                ],
                            ],
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Filtres de la liste des appels — le même jeu que `CallFilterRequest`.
     *
     * @return list<array<string, mixed>>
     */
    private function callFilterParameters(): array
    {
        return [
            $this->queryParameter('search', 'string', 'Recherche sur le client, son téléphone ou les notes'),
            $this->queryParameter('agent', 'integer', 'Identifiant de l\'agent ayant traité l\'appel'),
            $this->queryParameter('client', 'integer', 'Identifiant du client concerné'),
            $this->queryParameter('status', 'string', 'Statut de l\'appel', CallStatus::class),
            $this->queryParameter('reason', 'string', 'Motif de l\'appel', CallReason::class),
            $this->queryParameter('direction', 'string', 'Sens de l\'appel', CallDirection::class),
            $this->queryParameter('tag', 'string', 'Slug d\'une étiquette'),
            $this->queryParameter('from', 'string', 'Début de période (AAAA-MM-JJ)', format: 'date'),
            $this->queryParameter('to', 'string', 'Fin de période (AAAA-MM-JJ)', format: 'date'),
        ];
    }

    /**
     * @param  class-string<CallDirection|CallReason|CallStatus|ReservationStatus>|null  $enum
     * @return array<string, mixed>
     */
    private function queryParameter(string $name, string $type, string $description, ?string $enum = null, ?string $format = null): array
    {
        $schema = ['type' => $type];

        if ($enum !== null) {
            // Lu sur l'enum PHP : ajouter un motif met la documentation à jour.
            $schema['enum'] = array_column($enum::options(), 'value');
        }

        if ($format !== null) {
            $schema['format'] = $format;
        }

        return [
            'name' => $name,
            'in' => 'query',
            'description' => $description,
            'schema' => $schema,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function idParameter(string $name, string $description): array
    {
        return [
            'name' => $name,
            'in' => 'path',
            'required' => true,
            'description' => $description,
            'schema' => ['type' => 'integer'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function perPageParameter(): array
    {
        return [
            'name' => 'per_page',
            'in' => 'query',
            'description' => 'Nombre d\'éléments par page (25 par défaut)',
            'schema' => ['type' => 'integer', 'default' => 25],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pageParameter(): array
    {
        return [
            'name' => 'page',
            'in' => 'query',
            'description' => 'Page demandée',
            'schema' => ['type' => 'integer', 'default' => 1],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paginatedResponse(string $schema, string $description): array
    {
        return [
            'description' => $description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => ['type' => 'array', 'items' => ['$ref' => "#/components/schemas/{$schema}"]],
                            'links' => ['$ref' => '#/components/schemas/PaginationLinks'],
                            'meta' => ['$ref' => '#/components/schemas/PaginationMeta'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function singleResponse(string $schema, string $description): array
    {
        return [
            'description' => $description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => ['data' => ['$ref' => "#/components/schemas/{$schema}"]],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schemas(): array
    {
        return [
            'EnumValue' => [
                'type' => 'object',
                'description' => 'Valeur pilotant les filtres, libellé destiné à l\'affichage.',
                'properties' => [
                    'value' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                ],
            ],
            'Tag' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'examples' => ['urgent']],
                    'slug' => ['type' => 'string', 'examples' => ['urgent']],
                ],
            ],
            'Agent' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'examples' => ['Aïcha Bamba']],
                ],
            ],
            'Client' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'full_name' => ['type' => 'string', 'examples' => ['Aya Kouamé']],
                    'phone' => ['type' => 'string', 'examples' => ['+225 07 12 34 56 78']],
                    'email' => ['type' => ['string', 'null'], 'format' => 'email'],
                    'calls_count' => ['type' => 'integer'],
                    'reservations_count' => ['type' => 'integer'],
                    'reservations' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/Reservation'],
                    ],
                ],
            ],
            'Reservation' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'vehicle' => ['type' => 'string', 'examples' => ['Toyota RAV4']],
                    'starts_at' => ['type' => 'string', 'format' => 'date-time'],
                    'ends_at' => ['type' => 'string', 'format' => 'date-time'],
                    'status' => ['$ref' => '#/components/schemas/EnumValue'],
                    'calls_count' => ['type' => 'integer'],
                ],
            ],
            'Call' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'direction' => ['$ref' => '#/components/schemas/EnumValue'],
                    'reason' => ['$ref' => '#/components/schemas/EnumValue'],
                    'status' => ['$ref' => '#/components/schemas/EnumValue'],
                    'called_at' => ['type' => 'string', 'format' => 'date-time'],
                    'duration_seconds' => ['type' => 'integer', 'examples' => [252]],
                    'notes' => ['type' => ['string', 'null']],
                    'client' => ['$ref' => '#/components/schemas/Client'],
                    'agent' => ['$ref' => '#/components/schemas/Agent'],
                    'reservation' => [
                        'description' => 'Réservation rattachée, ou null si l\'appel n\'en concerne aucune.',
                        'oneOf' => [
                            ['$ref' => '#/components/schemas/Reservation'],
                            ['type' => 'null'],
                        ],
                    ],
                    'tags' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Tag']],
                    'can' => [
                        'type' => 'object',
                        'description' => 'Droits de l\'utilisateur courant sur cet appel.',
                        'properties' => [
                            'update' => ['type' => 'boolean'],
                            'delete' => ['type' => 'boolean'],
                        ],
                    ],
                ],
            ],
            'Statistics' => [
                'type' => 'object',
                'properties' => [
                    'period' => [
                        'type' => 'object',
                        'properties' => [
                            'from' => ['type' => 'string', 'format' => 'date'],
                            'to' => ['type' => 'string', 'format' => 'date'],
                            'granularity' => ['type' => 'string', 'enum' => ['day', 'week']],
                        ],
                    ],
                    'summary' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                            'average_duration' => ['type' => 'integer', 'description' => 'En secondes'],
                            'total_duration' => ['type' => 'integer', 'description' => 'En secondes'],
                            'resolved' => ['type' => 'integer'],
                            'escalated' => ['type' => 'integer'],
                        ],
                    ],
                    'volume' => [
                        'type' => 'object',
                        'description' => 'Série temporelle, jours sans appel inclus à zéro.',
                        'properties' => [
                            'labels' => ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'date']],
                            'values' => ['type' => 'array', 'items' => ['type' => 'integer']],
                        ],
                    ],
                    'by_reason' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Distribution']],
                    'by_status' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Distribution']],
                    'agent_ranking' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'name' => ['type' => 'string'],
                                'total' => ['type' => 'integer'],
                                'average_duration' => ['type' => 'integer'],
                            ],
                        ],
                    ],
                ],
            ],
            'Distribution' => [
                'type' => 'object',
                'properties' => [
                    'value' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                    'total' => ['type' => 'integer'],
                ],
            ],
            'PaginationLinks' => [
                'type' => 'object',
                'properties' => [
                    'first' => ['type' => ['string', 'null']],
                    'last' => ['type' => ['string', 'null']],
                    'prev' => ['type' => ['string', 'null']],
                    'next' => ['type' => ['string', 'null']],
                ],
            ],
            'PaginationMeta' => [
                'type' => 'object',
                'properties' => [
                    'current_page' => ['type' => 'integer'],
                    'from' => ['type' => ['integer', 'null']],
                    'last_page' => ['type' => 'integer'],
                    'per_page' => ['type' => 'integer'],
                    'to' => ['type' => ['integer', 'null']],
                    'total' => ['type' => 'integer'],
                ],
            ],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'errors' => [
                        'type' => 'object',
                        'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function responses(): array
    {
        return [
            'Unauthenticated' => [
                'description' => 'Session absente ou expirée',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => ['message' => ['type' => 'string', 'examples' => ['Unauthenticated.']]],
                        ],
                    ],
                ],
            ],
            'NotFound' => [
                'description' => 'Ressource introuvable',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => ['message' => ['type' => 'string']],
                        ],
                    ],
                ],
            ],
            'ValidationError' => [
                'description' => 'Paramètres refusés par la validation',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                    ],
                ],
            ],
        ];
    }
}
