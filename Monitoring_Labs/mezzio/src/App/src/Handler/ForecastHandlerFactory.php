<?php
namespace App\Handler;

use Demo\Weather\Forecast;
use Demo\Geonames\Random;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ForecastHandlerFactory
{
    public function __invoke(ContainerInterface $container) : ForecastHandler
    {
        return new ForecastHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(Forecast::class),
            $container->get(Random::class));
    }
}
