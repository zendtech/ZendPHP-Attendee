<?php
namespace App\Handler;

use App\Postcode;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class QueryHandlerFactory
{
    public function __invoke(ContainerInterface $container) : QueryHandler
    {
        return new QueryHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(Postcode::class));
    }
}
