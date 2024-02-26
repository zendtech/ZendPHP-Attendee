<?php
namespace App\Handler;

use App\Postcode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
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
        $this->renderer = $renderer;
        $this->postcode = $postcode;
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
        $result = $this->postcode->lookup($city, $state);
        $accept = current($request->getHeader('Accept') ?? []);
        if (!empty($accept) && str_contains($accept, 'json')) {
            $response = new JsonResponse($result);
        } else {
            $response = new HtmlResponse($this->renderer->render('app::query', ['result' => $result]));
        }
        return $response;
    }
}
