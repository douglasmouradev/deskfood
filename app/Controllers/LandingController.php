<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Landing de marketing (vitrine do produto), separada da home operacional de unidades.
 */
final class LandingController extends Controller
{
    /**
     * Página institucional com proposta de valor e CTAs para cliente / loja / dono.
     */
    public function index(): void
    {
        $this->view('landing/index', [
            'title' => 'Desk Food — Delivery próprio para restaurantes | Sem comissão por pedido',
            'metaDescription' => 'Lance delivery com sua marca: cardápio online, PIX e cartão automáticos, painel da cozinha e rastreio. Pare de perder margem para marketplace. Demonstração gratuita.',
            'csrf' => \App\Helpers\Csrf::token(),
        ], 'landing');
    }
}
