<?php

declare(strict_types=1);

namespace Postcode;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class QueryHandlerFactory
{
    public function __invoke(ContainerInterface $container) : QueryHandler
    {
        return new QueryHandler($container->get(TemplateRendererInterface::class));
    }
}
