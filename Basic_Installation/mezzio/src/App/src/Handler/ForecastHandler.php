<?php
namespace App\Handler;

use Demo\Weather\Forecast;
use Demo\Geonames\Random;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Template\TemplateRendererInterface;

class ForecastHandler implements RequestHandlerInterface
{
    public function __construct(
        public TemplateRendererInterface $renderer,
        public Forecast $forecast,
        public Random $random)
    {}

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        // Pick random city
        $city   = $this->random->pickCity('US');
        $error  = [];
        $output = '';
        $output .= "Random City Info:\n";
        $output .= var_export($city, TRUE) . PHP_EOL;
        // Weather Forecast for Random City
        if (!empty($city[2])) {
            $name = $city[2];
            $lat  = $city[3];
            $lon  = $city[4];
            $output .= "Weather forecast for $name\n";
            $output .= (new Forecast())->getForecast($lat, $lon, $error);
        }
        $accept = current($request->getHeader('Accept') ?? []);
        if (!empty($accept) && str_contains($accept, 'json')) {
            $response = new JsonResponse(['output' => $output, 'error' => $error]);
        } else {
            $response = new HtmlResponse($this->renderer->render('app::forecast', ['output' => $output, 'error' => $error]));
        }
        return $response;
    }
}
