<?php

declare(strict_types=1);

namespace Noesis\Dianoia\App;

use Aura\Session\Segment;
use Aura\Session\Session;
use Aura\Session\SessionFactory;
use DI\DependencyException;
use DI\NotFoundException;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Laminas\EventManager\EventManager;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Noesis\Dianoia\Provider\DebugBar\Traits\DebugBarTrait;
use Noesis\Dianoia\Support\Traits\LoggerTrait;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;

class Application extends ApplicationKernel
{
    use DebugBarTrait;
    use LoggerTrait;

    /**
     * @var Manager
     */
    protected Manager $eloquent;

    /**
     * Constructor.
     *
     * @param array $server
     * @param array $get
     * @param array $post
     * @param array $cookie
     * @param array $files
     */
    public function __construct(array $server, array $get, array $post, array $cookie, array $files)
    {
        parent::__construct($server, $get, $post, $cookie, $files);

        $this->loadWhoops(PrettyPageHandler::EDITOR_PHPSTORM);
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
     * Set Eloquent instance.
     *
     * @param Manager $eloquent
     */
    public function setEloquent(Manager $eloquent): void
    {
        $this->eloquent = $eloquent;
    }

    /**
     * Get Eloquent instance.
     *
     * @return Manager
     */
    public function getEloquent(): Manager
    {
        return $this->eloquent;
    }

    /**
     * Boot Database
     *
     * @return void
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function loadEloquent(): void
    {
        $this->setEloquent(new Manager());
        $config = $this->getConfig('database')->toArray();

        collect($config)->each(function ($env) {
            $this->getEloquent()->addConnection($env);
        });

        $this->getEloquent()->setEventDispatcher(new Dispatcher(new \Illuminate\Container\Container()));
        $this->getEloquent()->setAsGlobal();
        $this->getEloquent()->bootEloquent();
    }
}
