<?php
namespace App\Handler;

use Demo\Number\Prime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Template\TemplateRendererInterface;

class PrimeHandler implements RequestHandlerInterface
{
    public function __construct(
        public TemplateRendererInterface $renderer,
        public Prime $prime)
    {}

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $start  = rand(100_000, 500_000);
        $end    = $start + rand(0, 100_000);
        $output = implode(':', iterator_to_array($this->prime->make($start, $end)));
        $accept = current($request->getHeader('Accept') ?? []);
        if (!empty($accept) && str_contains($accept, 'json')) {
            $response = new JsonResponse(['output' => $output, 'error' => $error]);
        } else {
            $response = new HtmlResponse($this->renderer->render('app::prime', ['output' => $output, 'error' => $error]));
        }
        return $response;
    }
}
