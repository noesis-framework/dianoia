<?php

declare(strict_types=1);

namespace Noesis\Dianoia\Container;

use Noesis\Dianoia\Container\Exception\NotFoundException;
use DI\ContainerBuilder;
use DI\Definition\Helper\DefinitionHelper;
use Exception;
use Laminas\Di\Injector;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @var \DI\Container $container
     */
    private \DI\Container $container;

    /**
     * @var Injector $injector
     */
    private Injector $injector;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->setConfig(new Config(include dirname(__DIR__, 2) . '/config/app.php'));
        $this->setInjector(new Injector(null, $this));

        $containerBuilder = new ContainerBuilder();

        $this->setContainer($containerBuilder->build());
    }

    /**
     * Set Injector.
     *
     * @param Injector $injector
     */
    public function setInjector(Injector $injector): void
    {
        $this->injector = $injector;
    }

    /**
     * Get Injeector.
     *
     * @return Injector
     */
    public function getInjector(): Injector
    {
        return $this->injector;
    }

    /**
     * Set Config.
     *
     * @param Config $config
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * Get Config.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Set Container.
     *
     * @param \DI\Container $container
     */
    public function setContainer(\DI\Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Get container.
     *
     * @return \DI\Container
     */
    public function getContainer(): \DI\Container
    {
        return $this->container;
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string $name Name of service
     * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
     */
    public function set(string $name, mixed $value)
    {
        $this->getContainer()->set($name, $value);
    }

    /**
     * @param string $id Name of service
     *
     * @return mixed|string
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new NotFoundException("Could not find a service in container with name: $id.");
        }

        return $this->getContainer()->get($id);
    }

    /**
     * Check if container has a service with that name.
     *
     * @param string $id Name of service
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return ($this->getContainer()->has($id) || $this->getInjector()->canCreate($id));
    }
}