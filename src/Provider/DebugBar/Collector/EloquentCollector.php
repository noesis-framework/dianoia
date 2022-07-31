<?php

declare(strict_types=1);

namespace Noesis\Dianoia\Provider\DebugBar\Collector;

use Closure;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use Illuminate\Database\Capsule\Manager;
use JetBrains\PhpStorm\ArrayShape;
use PDO;

class EloquentCollector extends PDOCollector
{
    /**
     * @var Manager
     */
    protected Manager $eloquent;

    /**
     * Constructor.
     */
    public function __construct(Manager $eloquent)
    {
        parent::__construct();
        $this->setEloquent($eloquent);
    }

    public function boot()
    {
        $this->addConnection($this->getTraceablePdo(), 'Eloquent PDO');
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'eloquent_pdo';
    }

    /**
     * Get Widgets.
     *
     * @return array
     */
    #[ArrayShape(['eloquent' => "string[]", 'eloquent:badge' => "array"])]
    public function getWidgets(): array
    {
        return [
            'eloquent' => [
                'icon'    => 'inbox',
                'widget'  => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                'map'     => 'eloquent_pdo',
                'default' => '[]'
            ],
            'eloquent:badge' => [
                'map'       => 'eloquent_pdo.nb_statements',
                'default'   => 0
            ]
        ];
    }

    /**
     * @param Manager $eloquent
     *
     * @return void
     */
    public function setEloquent(Manager $eloquent): void
    {
        $this->eloquent = $eloquent;
    }

    /**
     * Get Eloquent.
     *
     * @return Manager
     */
    public function getEloquent(): Manager
    {
        return $this->eloquent;
    }

    /**
     * Get Eloquent PDO.
     *
     * @return PDO|Closure
     */
    protected function getEloquentPdo(): PDO|Closure
    {
        return $this->getEloquent()->getConnection()->getPdo();
    }

    /**
     * Get Traceable PDO.
     *
     * @return TraceablePDO
     */
    protected function getTraceablePdo(): TraceablePDO
    {
        return new TraceablePDO($this->getEloquentPdo());
    }
}
