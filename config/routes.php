<?php

declare(strict_types=1);

use App\Controllers\AdminAuthController;
use App\Controllers\HealthController;
use App\Controllers\AdminDashboardController;
use App\Controllers\AdminUnitController;
use App\Controllers\CustomerAuthController;
use App\Controllers\CustomerCartController;
use App\Controllers\CustomerCheckoutController;
use App\Controllers\CustomerLgpdController;
use App\Controllers\CustomerMenuController;
use App\Controllers\CustomerOrdersController;
use App\Controllers\HelpController;
use App\Controllers\HomeController;
use App\Controllers\LandingController;
use App\Controllers\MotoboyDeliveryController;
use App\Controllers\OperatorAuthController;
use App\Controllers\OperatorCashController;
use App\Controllers\OperatorDashboardController;
use App\Controllers\OperatorMenuController;
use App\Controllers\OperatorMotoboyController;
use App\Controllers\OnboardingController;
use App\Controllers\OperatorOrderController;
use App\Controllers\OperatorReportsController;
use App\Controllers\PageController;
use App\Controllers\TrackController;
use App\Controllers\WebhookController;

/**
 * Mapa declarativo de rotas HTTP do Desk Food.
 *
 * Cada entrada define métodos aceitos, caminho (com placeholders `{token}`),
 * handler `Class::method` e middlewares nomeados resolvidos pelo `Router`.
 */
return [
    ['methods' => ['GET'], 'path' => '/health', 'handler' => [HealthController::class, 'index']],
    ['methods' => ['GET'], 'path' => '/', 'handler' => [HomeController::class, 'index']],
    ['methods' => ['GET'], 'path' => '/landing', 'handler' => [LandingController::class, 'index']],
    ['methods' => ['GET'], 'path' => '/termos', 'handler' => [PageController::class, 'terms']],
    ['methods' => ['GET'], 'path' => '/privacidade', 'handler' => [PageController::class, 'privacy']],
    ['methods' => ['GET'], 'path' => '/ajuda', 'handler' => [HelpController::class, 'index']],

    ['methods' => ['GET'], 'path' => '/cliente/login', 'handler' => [CustomerAuthController::class, 'showLogin']],
    ['methods' => ['POST'], 'path' => '/cliente/login', 'handler' => [CustomerAuthController::class, 'sendOtp']],
    ['methods' => ['GET'], 'path' => '/cliente/verificar', 'handler' => [CustomerAuthController::class, 'showVerify']],
    ['methods' => ['POST'], 'path' => '/cliente/verificar', 'handler' => [CustomerAuthController::class, 'verify']],
    ['methods' => ['GET'], 'path' => '/cliente/sair', 'handler' => [CustomerAuthController::class, 'logout']],

    ['methods' => ['GET'], 'path' => '/u/{slug}', 'handler' => [CustomerMenuController::class, 'index']],
    ['methods' => ['POST'], 'path' => '/cliente/carrinho/adicionar', 'handler' => [CustomerCartController::class, 'add']],
    ['methods' => ['GET'], 'path' => '/cliente/carrinho', 'handler' => [CustomerCartController::class, 'index']],
    ['methods' => ['POST'], 'path' => '/cliente/carrinho/atualizar', 'handler' => [CustomerCartController::class, 'update']],

    ['methods' => ['GET'], 'path' => '/cliente/checkout', 'handler' => [CustomerCheckoutController::class, 'form'], 'middleware' => ['customer_auth']],
    ['methods' => ['POST'], 'path' => '/cliente/checkout', 'handler' => [CustomerCheckoutController::class, 'submit'], 'middleware' => ['customer_auth']],

    ['methods' => ['GET'], 'path' => '/cliente/pedidos', 'handler' => [CustomerOrdersController::class, 'index'], 'middleware' => ['customer_auth']],
    ['methods' => ['GET'], 'path' => '/cliente/pedido/{id}/pix', 'handler' => [CustomerOrdersController::class, 'pix'], 'middleware' => ['customer_auth']],

    ['methods' => ['GET'], 'path' => '/cliente/lgpd', 'handler' => [CustomerLgpdController::class, 'index'], 'middleware' => ['customer_auth']],
    ['methods' => ['GET'], 'path' => '/cliente/lgpd/dados', 'handler' => [CustomerLgpdController::class, 'data'], 'middleware' => ['customer_auth']],
    ['methods' => ['GET'], 'path' => '/cliente/lgpd/exportar', 'handler' => [CustomerLgpdController::class, 'export'], 'middleware' => ['customer_auth']],
    ['methods' => ['GET'], 'path' => '/cliente/lgpd/editar', 'handler' => [CustomerLgpdController::class, 'editForm'], 'middleware' => ['customer_auth']],
    ['methods' => ['POST'], 'path' => '/cliente/lgpd/editar', 'handler' => [CustomerLgpdController::class, 'editSave'], 'middleware' => ['customer_auth']],
    ['methods' => ['POST'], 'path' => '/cliente/lgpd/excluir', 'handler' => [CustomerLgpdController::class, 'delete'], 'middleware' => ['customer_auth']],

    ['methods' => ['GET'], 'path' => '/acompanhar/{token}', 'handler' => [TrackController::class, 'page']],
    ['methods' => ['GET'], 'path' => '/api/pedido/{token}/status', 'handler' => [TrackController::class, 'poll']],

    ['methods' => ['POST'], 'path' => '/webhooks/pix', 'handler' => [WebhookController::class, 'pix']],

    ['methods' => ['GET'], 'path' => '/admin/login', 'handler' => [AdminAuthController::class, 'showLogin']],
    ['methods' => ['POST'], 'path' => '/admin/login', 'handler' => [AdminAuthController::class, 'login']],
    ['methods' => ['GET'], 'path' => '/admin/sair', 'handler' => [AdminAuthController::class, 'logout']],

    ['methods' => ['POST'], 'path' => '/admin/onboarding/dismiss', 'handler' => [OnboardingController::class, 'dismissAdmin'], 'middleware' => ['admin_auth']],

    ['methods' => ['GET'], 'path' => '/admin', 'handler' => [AdminDashboardController::class, 'index'], 'middleware' => ['admin_auth']],
    ['methods' => ['GET'], 'path' => '/admin/unidades', 'handler' => [AdminUnitController::class, 'index'], 'middleware' => ['admin_auth']],
    ['methods' => ['GET'], 'path' => '/admin/unidades/nova', 'handler' => [AdminUnitController::class, 'createForm'], 'middleware' => ['admin_auth']],
    ['methods' => ['POST'], 'path' => '/admin/unidades/nova', 'handler' => [AdminUnitController::class, 'createSave'], 'middleware' => ['admin_auth']],
    ['methods' => ['POST'], 'path' => '/admin/unidades/{id}/toggle', 'handler' => [AdminUnitController::class, 'toggle'], 'middleware' => ['admin_auth']],

    ['methods' => ['GET'], 'path' => '/operador/login', 'handler' => [OperatorAuthController::class, 'showLogin']],
    ['methods' => ['POST'], 'path' => '/operador/login', 'handler' => [OperatorAuthController::class, 'login']],
    ['methods' => ['GET'], 'path' => '/operador/sair', 'handler' => [OperatorAuthController::class, 'logout']],

    ['methods' => ['POST'], 'path' => '/operador/onboarding/dismiss', 'handler' => [OnboardingController::class, 'dismissOperator'], 'middleware' => ['operator_auth']],

    ['methods' => ['GET'], 'path' => '/operador', 'handler' => [OperatorDashboardController::class, 'index'], 'middleware' => ['operator_auth']],
    ['methods' => ['GET'], 'path' => '/operador/api/quadro-rev', 'handler' => [OperatorDashboardController::class, 'boardPoll'], 'middleware' => ['operator_auth']],
    ['methods' => ['GET'], 'path' => '/operador/relatorios/pedidos.csv', 'handler' => [OperatorReportsController::class, 'ordersCsv'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/pedidos/{id}/status', 'handler' => [OperatorOrderController::class, 'status'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/pedidos/{id}/motoboy', 'handler' => [OperatorOrderController::class, 'assign'], 'middleware' => ['operator_auth']],

    ['methods' => ['GET'], 'path' => '/operador/cardapio', 'handler' => [OperatorMenuController::class, 'index'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/cardapio/categoria', 'handler' => [OperatorMenuController::class, 'createCategory'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/cardapio/produto', 'handler' => [OperatorMenuController::class, 'createProduct'], 'middleware' => ['operator_auth']],

    ['methods' => ['GET'], 'path' => '/operador/motoboys', 'handler' => [OperatorMotoboyController::class, 'index'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/motoboys', 'handler' => [OperatorMotoboyController::class, 'create'], 'middleware' => ['operator_auth']],

    ['methods' => ['GET'], 'path' => '/operador/caixa', 'handler' => [OperatorCashController::class, 'index'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/caixa/abrir', 'handler' => [OperatorCashController::class, 'open'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/caixa/sangria', 'handler' => [OperatorCashController::class, 'withdraw'], 'middleware' => ['operator_auth']],
    ['methods' => ['POST'], 'path' => '/operador/caixa/fechar', 'handler' => [OperatorCashController::class, 'close'], 'middleware' => ['operator_auth']],

    ['methods' => ['GET'], 'path' => '/m/{token}', 'handler' => [MotoboyDeliveryController::class, 'index']],
    ['methods' => ['POST'], 'path' => '/m/{token}/entregue', 'handler' => [MotoboyDeliveryController::class, 'complete']],
];
