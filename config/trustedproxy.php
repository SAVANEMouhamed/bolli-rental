<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxys de confiance
    |--------------------------------------------------------------------------
    |
    | Lu par le middleware TrustProxies du framework. Derrière un proxy qui
    | termine TLS — OpenResty de 1Panel, puis le nginx de la composition —
    | Laravel doit croire les en-têtes X-Forwarded-* pour reconstruire le schéma
    | et l'hôte d'origine. Sans cela, `route()` fabrique des URL en http dans les
    | e-mails d'invitation, et `SESSION_SECURE_COOKIE` devient inopérant.
    |
    | La valeur « * » n'est sûre que parce que le conteneur n'est joignable que
    | par le proxy : il n'est publié que sur la boucle locale du VPS. En
    | développement la variable est absente, aucun proxy n'est donc approuvé.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
