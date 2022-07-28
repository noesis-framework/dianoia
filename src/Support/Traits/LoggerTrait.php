<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Support\Traits;

use Affinity4\Dianoia\App\App;
use DateTime;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\InvalidArgumentException;

trait LoggerTrait
{
    /**
     * @var string
     */
    protected string $log_channel = 'app';

    /**
     * @var Logger[] $loggers
     */
    protected array $loggers = [];

    /**
     * Set log channel.
     *
     * @param string $log_channel
     */
    public function setLogChannel(string $log_channel): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $this->log_channel = $log_channel;

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Get log channel.
     *
     * @return string
     */
    public function getLogChannel(): string
    {
        return $this->log_channel;
    }

    /**
     * Add a logger for channel.
     *
     * @param string $channel
     */
    public function addLogger(string $channel): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        if (array_key_exists($channel, $this->loggers)) {
            throw new InvalidArgumentException("Logger channel '$channel' already exists");
        }

        $this->loggers[$channel] = new Logger($channel);

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Get logger.
     *
     * @param string $channel
     *
     * @return Logger
     */
    public function getLogger(string $channel = 'app'): Logger
    {
        if (!array_key_exists($channel, $this->loggers)) {
            throw new InvalidArgumentException("Logger channel '$channel' does not exist");
        }

        return $this->loggers[$channel];
    }

    /**
     * Load the app logger channel.
     *
     * @return void
     */
    protected function loadAppLogger(): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $paths = function ($level) {
            $date = (new DateTime())->format('Y-m-d');
            return match ($level) {
                Level::Info => "{$this->root}/tmp/logs/app/info/$date.log",
                Level::Debug => "{$this->root}/tmp/logs/app/debug/$date.log",
                Level::Error => "{$this->root}/tmp/logs/app/error/$date.log",
            };
        };

        $this->addLogger($this->getLogChannel());

        $this->getLogger($this->getLogChannel())->pushHandler(new StreamHandler($paths(Level::Info), Level::Info));
        $this->getLogger($this->getLogChannel())->pushHandler(new StreamHandler($paths(Level::Debug), Level::Debug));
        $this->getLogger($this->getLogChannel())->pushHandler(new StreamHandler($paths(Level::Error), Level::Error));

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }
}
