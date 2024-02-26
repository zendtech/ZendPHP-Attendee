<?php
namespace App;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
use Demo\Weather\Forecast;
use Demo\Geonames\Random;
use Psr\Container\ContainerInterface;
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
                Postcode::class => Postcode::class,
                Forecast::class => Forecast::class,
                Random::class => Random::class,
            ],
            'factories'  => [
                Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,
                Handler\QueryHandler::class => Handler\QueryHandlerFactory::class,
                Handler\ForecastHandler::class => Handler\ForecastHandlerFactory::class,
                Handler\InfoHandler::class => Handler\InfoHandlerFactory::class,
                /*
                Connection::class => function (ContainerInterface $container) {
                    return new Connection(); },
                Postcode::class => function (ContainerInterface $container) {
                    return new Postcode($container->get(Connection::class)()); },
                */
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => [__DIR__ . '/../templates/app'],
                'error'  => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }
}
