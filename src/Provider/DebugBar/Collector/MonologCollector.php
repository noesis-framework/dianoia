<?php

declare(strict_types=1);

namespace Noesis\Dianoia\Provider\DebugBar\Collector;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;
use DebugBar\DataCollector\Renderable;
use JetBrains\PhpStorm\ArrayShape;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * A monolog handler as well as a data collector
 *
 * @link https://github.com/Seldaek/monolog
 *
 * <code>
 * $debugbar->addCollector(new MonologCollector($logger));
 * </code>
 */
class MonologCollector extends AbstractProcessingHandler implements
    DataCollectorInterface,
    Renderable,
    MessagesAggregateInterface
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var array
     */
    protected array $records = [];

    /**
     * @param Logger|null $logger
     * @param int         $level
     * @param bool        $bubble
     * @param string      $name
     */
    public function __construct(
        Logger $logger = null,
        $level = Level::Debug,
        bool $bubble = true,
        string $name = 'monolog'
    ) {
        parent::__construct($level, $bubble);
        $this->name = $name;
        if ($logger !== null) {
            $this->addLogger($logger);
        }
    }

    /**
     * Adds logger which messages you want to log
     *
     * @param Logger $logger
     */
    public function addLogger(Logger $logger)
    {
        $logger->pushHandler($this);
    }

    /**
     * Write a log record.
     *
     * @param LogRecord $record
     */
    protected function write(LogRecord $record): void
    {
    }

    /**
     * @ return array
     */
    public function getMessages()
    {
    }

    /**
     * @return array
     */
    #[ArrayShape(['count' => "int|void", 'records' => "array"])]
    public function collect(): array
    {
        return [
            'count' => count($this->records),
            'records' => $this->records
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getWidgets(): array
    {
        return [
            $this->getName() => [
                "icon" => "suitcase",
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "{$this->getName()}.records",
                "default" => "[]"
            ],
            "{$this->getName()}:badge" => [
                "map" => "{$this->getName()}.count",
                "default" => "null"
            ]
        ];
    }
}
