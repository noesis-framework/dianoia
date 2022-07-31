<?php

declare(strict_types=1);

namespace Noesis\Dianoia\App;

use DebugBar\DebugBarException;
use DI\DependencyException;
use DI\NotFoundException;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\Route;
use League\Route\RouteGroup;
use Noesis\Dianoia\Container\Container;
use Noesis\Dianoia\Provider\DebugBar\DebugBar;
use Noesis\Dianoia\Provider\DebugBar\Middleware\DebugBarMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * App class.
 *
 * This is the core of Noesis\Dianoia
 */
class App extends Application
{
    public const VERSION = '0.0.1';

    /**
     * Boot application service providers.
     *
     * @param array $config
     *
     * @return void
     *
     * @throws DebugBarException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function boot(array $config = ['app' => [], 'database' => []]): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        $this->setContainer(new Container());
        $this->loadSession();
        $this->loadAppLogger();
        $this->registerConfiguration($config);
        $this->loadEloquent();
        $this->registerServices();
        $this->loadRouter();
        $this->setDebugBar(new DebugBar($this));
        $this->loadRequest();
        $this->loadAppMiddlewares();

        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * Run the application.
     *
     * @return void
     */
    public function run(): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        $response = $this->getRouter()->dispatch($this->getRequest());
        (new SapiEmitter())->emit($response);

        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }
}
