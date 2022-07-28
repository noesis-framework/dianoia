<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Provider\DebugBar;

use Affinity4\Dianoia\App\App;
use Affinity4\Dianoia\Provider\DebugBar\Collector\MonologCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DI\DependencyException;
use DI\NotFoundException;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DebugBar as PhpDebugBar;
use DebugBar\DebugBarException;

class DebugBar extends PhpDebugBar
{
    /**
     * @var App|null
     */
    protected ?App $app;

    /**
     * Constructor.
     *
     * @param App $app
     *
     * @throws DebugBarException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->boot();
    }

    /**
     * Boot the debug bar.
     *
     * @return void
     * @throws DebugBarException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function boot(): void
    {
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MemoryCollector());

        $this->addCollector(new MessagesCollector());

        $requestDataCollector = new RequestDataCollector();
        $requestDataCollector->useHtmlVarDumper(true);
        $this->addCollector($requestDataCollector);

        $logger = $this->app->getLogger($this->app->getLogChannel());
        $this->addCollector(new MonologCollector($logger));

        $startTime = $this->app->getGlobalValue('server', 'request_time_float');
        $this->addCollector(new TimeDataCollector($startTime));
        $debugBar = $this;
        $this->app->addEventListener(
            APP::class . '::boot.after',
            function ($event) use ($debugBar, $startTime) {
                $debugBar['time']->addMeasure('Booted', $startTime, microtime(true));
            }
        );
        $this->app->addEventListener(
            APP::class . '::loadAppMiddlewares.before',
            function ($event) use ($debugBar, $startTime) {
                $debugBar['time']->addMeasure('App middleware loaded', $startTime, microtime(true));
            }
        );
        $this->app->addEventListener(
            APP::class . '::run.before',
            function ($event) use ($debugBar, $startTime) {
                $debugBar['time']->addMeasure('App rendered', $startTime, microtime(true));
            }
        );
    }
}
