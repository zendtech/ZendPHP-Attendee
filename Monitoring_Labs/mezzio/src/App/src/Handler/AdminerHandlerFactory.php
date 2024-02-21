<?php
namespace App\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class AdminerHandlerFactory
{
    public function __invoke(ContainerInterface $container) : AdminerHandler
    {
        return new AdminerHandler($container->get(TemplateRendererInterface::class));
    }
}
