<?php
namespace Postcode;

use App\Postcode;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
class BuildHandlerFactory
{
    public function __invoke(ContainerInterface $container) : BuildHandler
    {
        return new BuildHandler($container->get(TemplateRendererInterface::class),
                                $container->get(Postcode::class),
                                $container->get('data_dir'));
    }
}
