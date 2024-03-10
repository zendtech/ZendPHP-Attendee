<?php
namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Template\TemplateRendererInterface;

class InfoHandler implements RequestHandlerInterface
{
    public function __construct(public TemplateRendererInterface $renderer)
    {}

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return new HtmlResponse($this->renderer->render('app::info'));
    }
}
