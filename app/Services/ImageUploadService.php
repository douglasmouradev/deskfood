<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Logger;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/**
 * Upload seguro de imagens (JPEG/PNG/WebP) com validação por finfo e limite de tamanho.
 */
final class ImageUploadService
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Salva arquivo enviado em `public/uploads/products/` e retorna caminho relativo ao public/.
     *
     * @param array{name?:string,type?:string,tmp_name?:string,size?:int,error?:int}|null $file Entrada de $_FILES
     * @return string|null Caminho tipo `uploads/products/uuid.jpg` ou null se nenhum arquivo válido
     */
    public static function storeProductImage(?array $file, int $maxBytes, string $publicRoot): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erro no upload da imagem.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new RuntimeException('Imagem excede o tamanho máximo permitido.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Upload inválido.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if ($mime === false || !isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Tipo de imagem não permitido (use JPEG, PNG ou WebP).');
        }

        $ext = self::ALLOWED[$mime];
        $dir = rtrim($publicRoot, '/') . '/uploads/products';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar diretório de uploads.');
        }

        $name = Uuid::uuid4()->toString() . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            Logger::log('error', 'Falha ao mover upload', ['dest' => $dest]);

            throw new RuntimeException('Não foi possível salvar a imagem.');
        }

        return 'uploads/products/' . $name;
    }
}
