<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Provider\DebugBar\Collector;

use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\MessagesAggregateInterface;
use DebugBar\DataCollector\Renderable;
use JetBrains\PhpStorm\ArrayShape;
use Monolog\Handler\AbstractProcessingHandler;
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
    protected $name;

    /**
     * @var array
     */
    protected $records = [];

    /**
     * @param Logger|null $logger
     * @param int         $level
     * @param boolean     $bubble
     * @param string      $name
     */
    public function __construct(Logger $logger = null, $level = Logger::DEBUG, $bubble = true, $name = 'monolog')
    {
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
        // $this->records[] = [
        //     'message' => $record['formatted'],
        //     'is_string' => true,
        //     'label' => strtolower($record['level_name']),
        //     'time' => $record['datetime']->format('U')
        // ];
    }

    /**
     * @ return array
     */
    public function getMessages()
    {
        // return $this->records;
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
        $name = $this->getName();
        return [
            $name => [
                "icon" => "suitcase",
                "widget" => "PhpDebugBar.Widgets.MessagesWidget",
                "map" => "$name.records",
                "default" => "[]"
            ],
            "$name:badge" => [
                "map" => "$name.count",
                "default" => "null"
            ]
        ];
    }
}
