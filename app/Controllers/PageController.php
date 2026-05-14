<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Páginas institucionais (termos e política de privacidade / LGPD).
 */
final class PageController extends Controller
{
    /**
     * Termos de uso com versão configurável.
     */
    public function terms(): void
    {
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $this->view('pages/terms', ['config' => $cfg, 'title' => 'Termos de Uso'], 'public');
    }

    /**
     * Política de privacidade completa em português.
     */
    public function privacy(): void
    {
        $cfg = require dirname(__DIR__, 2) . '/config/app.php';
        $this->view('pages/privacy', ['config' => $cfg, 'title' => 'Política de Privacidade'], 'public');
    }
}
