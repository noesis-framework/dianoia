<?php

declare(strict_types=1);

namespace Noesis\Dianoia\App;

use DI\DependencyException;
use DI\NotFoundException;
use InvalidArgumentException;
use Laminas\Config\Config;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\EventManager\EventManager;
use Noesis\Dianoia\Container\Container;
use Noesis\Dianoia\Support\Traits\EventTrait;
use Noesis\Dianoia\Support\Traits\ServicesTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ApplicationService
{
    use EventTrait;
    use ServicesTrait;

    /**
     * @var Container $container
     */
    protected Container $container;

    /**
     * @var string
     */
    protected string $root;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->root = dirname(__DIR__, 2);
        $this->setEventManager(new EventManager());
    }

    /**
     * Register Configuration
     *
     * @param array $config
     *
     * @return void
     */
    protected function registerConfiguration(array $config = []): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        if (!array_key_exists('app', $config)) {
            throw new InvalidArgumentException("Config array must have top level key 'app'");
        }

        if (!array_key_exists('database', $config)) {
            throw new InvalidArgumentException("Config array must have top level key 'database'");
        }

        $this->getContainer()->set('config.app', new Config($config['app']));
        $this->getContainer()->set('config.database', new Config($config['database']));

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function registerServices(): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $this->getContainer()->set(ResponseFactoryInterface::class, new ResponseFactory());
        $this->getContainer()->set(ResponseInterface::class, new Response());

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Set container.
     *
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Get container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get Config instance.
     *
     * @param string $key
     *
     * @return Config
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getConfig(string $key = 'app'): Config
    {
        return $this->getContainer()->get("config.$key");
    }
}
