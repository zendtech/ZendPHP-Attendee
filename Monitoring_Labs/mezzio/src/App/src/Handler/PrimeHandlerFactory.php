<?php
namespace App\Handler;

use Demo\Number\Prime;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class PrimeHandlerFactory
{
    public function __invoke(ContainerInterface $container) : PrimeHandler
    {
        return new PrimeHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(Prime::class));
    }
}
