<?php
namespace App\Handler;

use App\Postcode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Template\TemplateRendererInterface;

class QueryHandler implements RequestHandlerInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;
    public ?Lookup $lookup = NULL;
    public function __construct(TemplateRendererInterface $renderer, Postcode $postcode)
    {
        $this->postcode = $postcode;
        $this->renderer = $renderer;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $data   = [];
        $params = $request->getQueryParams();
        $city   = trim(strip_tags($params['city'] ?? ''));
        $state  = trim(strip_tags($params['state'] ?? ''));
        if (empty($city)) {
            $row = $this->postcode->getRandomPostcode();
            $city = $row[2] ?? '';
            $state = $row[4] ?? '';
        }
        // Render and return a response:
        return new JsonResponse($this->postcode->lookup($city, $state));
    }
}
