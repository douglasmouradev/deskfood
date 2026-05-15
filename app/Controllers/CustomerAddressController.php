<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Helpers\Csrf;
use App\Helpers\Redirect;
use App\Services\AddressService;

/**
 * Endereços salvos do cliente.
 */
final class CustomerAddressController extends Controller
{
    public function index(): void
    {
        $uid = (int) $_SESSION['user_id'];
        $this->view('customer/addresses', [
            'addresses' => AddressService::listForUser($uid),
            'csrf' => Csrf::token(),
            'title' => 'Meus endereços',
        ], 'customer');
    }

    public function save(): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/enderecos');
        }

        $uid = (int) $_SESSION['user_id'];
        $data = [
            'street' => trim((string) filter_input(INPUT_POST, 'street', FILTER_UNSAFE_RAW)),
            'number' => trim((string) filter_input(INPUT_POST, 'number', FILTER_UNSAFE_RAW)),
            'complement' => trim((string) (filter_input(INPUT_POST, 'complement', FILTER_UNSAFE_RAW) ?: '')),
            'neighborhood' => trim((string) filter_input(INPUT_POST, 'neighborhood', FILTER_UNSAFE_RAW)),
            'city' => trim((string) filter_input(INPUT_POST, 'city', FILTER_UNSAFE_RAW)),
            'state' => strtoupper(trim((string) filter_input(INPUT_POST, 'state', FILTER_UNSAFE_RAW))),
            'zip' => trim((string) filter_input(INPUT_POST, 'zip', FILTER_UNSAFE_RAW)),
        ];

        foreach (['street', 'number', 'neighborhood', 'city', 'state', 'zip'] as $k) {
            if ($data[$k] === '') {
                $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios.';
                Redirect::to('/cliente/enderecos');
            }
        }

        AddressService::saveFromCheckout($uid, $data, filter_input(INPUT_POST, 'is_default', FILTER_VALIDATE_BOOL) ?? false);
        $_SESSION['flash_ok'] = 'Endereço salvo.';
        Redirect::to('/cliente/enderecos');
    }

    public function delete(int $id): void
    {
        if (!Csrf::validate()) {
            Redirect::to('/cliente/enderecos');
        }

        $uid = (int) $_SESSION['user_id'];
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM user_addresses WHERE id = :id AND user_id = :u')->execute(['id' => $id, 'u' => $uid]);
        Redirect::to('/cliente/enderecos');
    }
}
