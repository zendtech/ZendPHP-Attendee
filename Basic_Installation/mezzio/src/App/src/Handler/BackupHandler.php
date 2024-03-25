<?php

declare(strict_types=1);

namespace App\Handler;

use App\Postcode;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function time;

class BackupHandler implements RequestHandlerInterface
{
    public const CODE = '123456';
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $code    = $request->getHeader('Backup-Code');
        if (empty($code) || $code[0] !== self::CODE) {
            $status = 401;
            $txt    = 'Unable to process request';
        } else {
            $src_fn  = Postcode::DATA_DIR . Postcode::DB_FN;
            $dest_fn = $src_fn . '_' . date('Y-m-d-H-i');
            $result  = copy($src_fn, $dest_fn);
            if ($result === TRUE) {
                $status = 201;
                $txt    = 'SUCCESS: Backed up to: ' . $dest_fn;
            } else {
                $status = 202;
                $txt    = 'ERROR: Unable to backup to: ' . $dest_fn;
            }
        }
        return new JsonResponse(['status' => $status, 'text' => $txt]);
    }
}
