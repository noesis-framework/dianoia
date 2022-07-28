<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\App;

use Affinity4\Dianoia\Container\Container;
use Affinity4\Dianoia\Provider\DebugBar\DebugBar;
use Affinity4\Dianoia\Provider\DebugBar\Middleware\DebugBarMiddleware;
use Affinity4\Dianoia\Provider\DebugBar\Traits\DebugBarTrait;
use Affinity4\Dianoia\Support\Traits\ContainerTrait;
use Affinity4\Dianoia\Support\Traits\EventTrait;
use Affinity4\Dianoia\Support\Traits\LoggerTrait;
use Affinity4\Dianoia\Support\Traits\RouterTrait;
use Affinity4\Dianoia\Support\Traits\ServicesTrait;
use Affinity4\Dianoia\Support\Traits\SessionTrait;
use DebugBar\DebugBarException;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use InvalidArgumentException;
use Laminas\Config\Config;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\EventManager\EventManager;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\Route;
use League\Route\RouteGroup;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

/**
 * App class.
 *
 * This is the core of Affinity4\Dianoia
 */
class App
{
    use EventTrait;
    use DebugBarTrait;
    use RouterTrait;
    use SessionTrait;
    use ContainerTrait;
    use LoggerTrait;
    use ServicesTrait;

    public const VERSION = '0.0.1';

    public const SERVER = 'server';
    public const GET = 'get';
    public const POST = 'post';
    public const COOKIE = 'cookie';
    public const FILES = 'files';

    /**
     * @var ServerRequestInterface $request
     */
    protected ServerRequestInterface $request;

    /**
     * @var string
     */
    protected string $root;

    /**
     * Constructor.
     *
     * @param array $server
     * @param array $get
     * @param array $post
     * @param array $cookie
     * @param array $files
     */
    public function __construct(
        private readonly array $server,
        private readonly array $get,
        private readonly array $post,
        private readonly array $cookie,
        private readonly array $files
    ) {
        $this->root = dirname(__DIR__, 2);
        $this->loadWhoops(PrettyPageHandler::EDITOR_PHPSTORM);
        $this->setEventManager(new EventManager());
    }

    /**
     * Get Global Value.
     *
     * @param string $global
     * @param string $key
     *
     * @return mixed
     */
    public function getGlobalValue(string $global, string $key): mixed
    {
        return match ($global) {
            self::SERVER => $this->server[strtoupper($key)],
            self::GET => $this->get[$key],
            self::POST => $this->post[$key],
            self::COOKIE => $this->cookie[$key],
            self::FILES => $this->files[$key]
        };
    }

    /**
     * Get Config instance.
     *
     * @return Config
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getConfig(string $key = 'app'): Config
    {
        return $this->getContainer()->get("config.{$key}");
    }

    /**
     * Set server request.
     *
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);
        $this->request = $request;
        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * Get server request.
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Load Whoops.
     *
     * @param string $editor
     *
     * @return void
     */
    protected function loadWhoops(string $editor): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        $handler = new PrettyPageHandler();
        $handler->setEditor($editor);

        $whoops = new Whoops();
        $whoops->pushHandler($handler);
        $whoops->register();

        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * @return void
     */
    public function loadRequest(): void
    {
        $this->setRequest(ServerRequestFactory::fromGlobals(
            $this->server,
            $this->get,
            $this->post,
            $this->cookie,
            $this->files
        ));
        $this->setRequest($this->getRequest()->withAttribute(App::class, $this));
    }

    /**
     * Boot Database
     *
     * @return void
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function loadEloquent(): void
    {
        $databaseManager = new Manager();
        $config = $this->getConfig('database')->toArray();

        collect($config)->each(function ($env, $key) use (&$databaseManager) {
            $databaseManager->addConnection($env);
        });

        $databaseManager->setEventDispatcher(new Dispatcher(new \Illuminate\Container\Container()));
        $databaseManager->setAsGlobal();
        $databaseManager->bootEloquent();
    }

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
     * Create a GET route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function get(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->get($path, $handler);
    }

    /**
     * Create a POST route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function post(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->post($path, $handler);
    }

    /**
     * Create a DELETE route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function delete(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->delete($path, $handler);
    }

    /**
     * Create a PUT route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function put(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->put($path, $handler);
    }

    /**
     * Create a PATCH route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function patch(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->patch($path, $handler);
    }

    /**
     * Create an OPTIONS route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function options(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->map('OPTIONS', $path, $handler);
    }

    /**
     * Create a HEAD route.
     *
     * @param string   $path
     * @param callable $handler
     *
     * @return Route
     */
    public function head(string $path, callable $handler): Route
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->map('HEAD', $path, $handler);
    }

    /**
     * Create a group of routes.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return RouteGroup
     */
    public function group(string $prefix, callable $group): RouteGroup
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->group($prefix, $group);
    }

    /**
     * Add middleware to stack.
     *
     * @param MiddlewareInterface $middleware
     *
     * @return MiddlewareAwareInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareAwareInterface
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->middleware($middleware);
    }

    /**
     * Add an array of middlewares to the stack.
     *
     * @param array $middlewares
     *
     * @return MiddlewareAwareInterface
     */
    public function addMiddlewares(array $middlewares): MiddlewareAwareInterface
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        return $this->getRouter()->middlewares($middlewares);
    }

    /**
     * Load App Middleware.
     *
     * @return void
     */
    protected function loadAppMiddlewares(): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        $this->addMiddlewares([
            new DebugBarMiddleware()
        ]);

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
