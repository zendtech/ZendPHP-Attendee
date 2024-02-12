<?php
namespace Postcode;

use App\Postcode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

class BuildHandler implements RequestHandlerInterface
{
    public function __construct(public TemplateRendererInterface $renderer,
                                public Postcode $postcode)
    {}

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        // Do some work...
        // Render and return a response:
        return new HtmlResponse($this->renderer->render(
            'postcode::build',
            [] // parameters to pass to template
        ));
    }
}
