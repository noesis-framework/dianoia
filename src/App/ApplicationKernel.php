<?php

namespace Noesis\Dianoia\App;

use Aura\Session\Segment;
use Aura\Session\Session;
use Aura\Session\SessionFactory;
use Laminas\Diactoros\ServerRequestFactory;
use League\Route\Middleware\MiddlewareAwareInterface;
use League\Route\Route;
use League\Route\RouteGroup;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Noesis\Dianoia\Provider\DebugBar\Middleware\DebugBarMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class ApplicationKernel extends ApplicationService
{
    public const SERVER = 'server';
    public const GET = 'get';
    public const POST = 'post';
    public const COOKIE = 'cookie';
    public const FILES = 'files';

    /**
     * @var Session $sessionInstance
     */
    protected Session $sessionInstance;

    /**
     * @var Segment $session
     */
    protected Segment $session;

    /**
     * @var Router $router
     */
    protected Router $router;

    /**
     * @var ServerRequestInterface $request
     */
    protected ServerRequestInterface $request;

    /**
     * Get Global Value.
     *
     * @param string $global
     * @param string $key
     *
     * @return mixed
     */

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
        protected readonly array $server,
        protected readonly array $get,
        protected readonly array $post,
        protected readonly array $cookie,
        protected readonly array $files
    ) {
        parent::__construct();
    }

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
     * Set Session instance.
     *
     * @param Session $sessionInstance
     */
    public function setSessionInstance(Session $sessionInstance): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);
        $this->sessionInstance = $sessionInstance;
        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * Get Session instance.
     *
     * @return Session
     */
    public function getSessionInstance(): Session
    {
        return $this->sessionInstance;
    }

    /**
     * Set Session segment.
     *
     * @param Segment $session
     */
    public function setSession(Segment $session): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);
        $this->session = $session;
        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * Get current Session segment.
     *
     * @return Segment
     */
    public function getSession(): Segment
    {
        return $this->session;
    }

    /**
     * Load Session.
     *
     * @return void
     */
    protected function loadSession(): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        $this->setSessionInstance((new SessionFactory())->newInstance($this->cookie));
        $this->setSession($this->getSessionInstance()->getSegment(self::class));

        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /* -----
     * Router
     * ----- */
    /**
     * Set Router.
     *
     * @param Router $router
     */
    public function setRouter(Router $router): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $this->router = $router;

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Get Router.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Load Router.
     *
     * @return void
     */
    protected function loadRouter(): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $strategy = new ApplicationStrategy();
        $strategy->setContainer($this->getContainer());
        $leagueRouter = new Router();
        $leagueRouter->setStrategy($strategy);
        $this->setRouter($leagueRouter);

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
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
}
