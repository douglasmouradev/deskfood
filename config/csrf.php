<?php

declare(strict_types=1);

/**
 * Rotas POST isentas de CSRF (webhooks externos, health).
 *
 * @return list<string> Padrões regex (delimitador #)
 */
return [
    '#^/webhooks/#',
    '#^/health$#',
];
