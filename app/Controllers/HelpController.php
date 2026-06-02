<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Central de ajuda, FAQ e boas práticas para uso do Desk Food.
 */
final class HelpController extends Controller
{
    /**
     * Página pública de ajuda e primeiros passos.
     */
    public function index(): void
    {
        $this->view('help/index', [
            'title' => 'Central de ajuda',
            'metaDescription' => 'Central de ajuda Desk Food: dono, operador, cliente, LGPD, PIX e boas práticas de implantação.',
            'canonicalPath' => '/ajuda',
        ], 'public');
    }
}
