<?php
namespace App\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class InfoHandlerFactory
{
    public function __invoke(ContainerInterface $container) : InfoHandler
    {
        return new InfoHandler(
            $container->get(TemplateRendererInterface::class));
    }
}
