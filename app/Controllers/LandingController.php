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
            'title' => 'Desk Food — Delivery profissional para o seu restaurante',
            'metaDescription' => 'Desk Food: plataforma de delivery multi-unidade com PIX, caixa, motoboys, OTP e LGPD. Recursos, fluxo operacional e contato comercial.',
            'csrf' => \App\Helpers\Csrf::token(),
        ], 'landing');
    }
}
