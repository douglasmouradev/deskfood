<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Redirect;

/**
 * Onboarding: banners pós-login podem ser dispensados pelo usuário.
 */
final class OnboardingController extends Controller
{
    /**
     * Remove o checklist do painel do dono para a sessão atual.
     */
    public function dismissAdmin(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/admin');
        }

        unset($_SESSION['show_onboarding_admin']);
        Redirect::to('/admin');
    }

    /**
     * Remove o checklist do painel do operador para a sessão atual.
     */
    public function dismissOperator(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/operador');
        }

        unset($_SESSION['show_onboarding_operator']);
        Redirect::to('/operador');
    }
}
