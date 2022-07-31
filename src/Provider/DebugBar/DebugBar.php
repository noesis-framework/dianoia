<?php

declare(strict_types=1);

namespace Noesis\Dianoia\Provider\DebugBar;

use Noesis\Dianoia\App\App;
use Noesis\Dianoia\Provider\DebugBar\Collector\EloquentCollector;
use Noesis\Dianoia\Provider\DebugBar\Collector\MonologCollector;
use DebugBar\DataCollector\TimeDataCollector;
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
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        $this->boot();
    }

    /**
     * Add Monolog Collector.
     *
     * Adds Monolog tab to debug bar.
     *
     * @return void
     * @throws DebugBarException
     */
    private function addMonologCollector(): void
    {
        $logger = $this->app->getLogger($this->app->getLogChannel());
        $this->addCollector(new MonologCollector($logger));
    }

    /**
     * Add Time Collector.
     *
     * Adds Timeline to DebugBar.
     *
     * @return void
     * @throws DebugBarException
     */
    private function addTimeCollector(): void
    {
        $startTime = $this->app->getGlobalValue('server', 'request_time_float');
        $this->addCollector(new TimeDataCollector($startTime));
        $debugBar = $this;
        $this->app->addEventListener(
            APP::class . '::boot.after',
            function () use ($debugBar, $startTime) {
                $debugBar['time']->addMeasure('Booted', $startTime, microtime(true));
            }
        );
        $this->app->addEventListener(
            APP::class . '::loadAppMiddlewares.before',
            function () use ($debugBar, $startTime) {
                $debugBar['time']->addMeasure('App middleware loaded', $startTime, microtime(true));
            }
        );
        $this->app->addEventListener(
            APP::class . '::run.before',
            function () use ($debugBar, $startTime) {
                $debugBar['time']->addMeasure('App rendered', $startTime, microtime(true));
            }
        );
    }

    /**
     * Add Request Collector.
     *
     * Adds Request ($_GLOBALS) tab to DebugBar.
     *
     * @return void
     * @throws DebugBarException
     */
    private function addRequestCollector(): void
    {
        $requestDataCollector = new RequestDataCollector();
        $requestDataCollector->useHtmlVarDumper();
        $this->addCollector($requestDataCollector);
    }

    /**
     * @throws DebugBarException
     */
    private function addEloquentCollector(): void
    {
        $eloquentCollector = new EloquentCollector($this->app->getEloquent());
        $this->addCollector($eloquentCollector);
    }

    /**
     * Boot the debug bar.
     *
     * @return void
     * @throws DebugBarException
     */
    public function boot(): void
    {
        $this->addCollector(new PhpInfoCollector());
        $this->addCollector(new MemoryCollector());
        $this->addCollector(new MessagesCollector());
        $this->addRequestCollector();
        $this->addMonologCollector();
        $this->addTimeCollector();
        $this->addEloquentCollector();
    }
}
