<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Provider\DebugBar\Traits;

use Affinity4\Dianoia\Provider\DebugBar\DebugBar;

trait DebugBarTrait
{
    /**
     * @var DebugBar
     */
    protected DebugBar $debugBar;

    /**
     * Set Debug Bar.
     *
     * @param DebugBar $debugBar
     *
     * @return void
     */
    protected function setDebugBar(DebugBar $debugBar): void
    {
        $this->debugBar = $debugBar;
    }

    /**
     * Get Debug Bar.
     *
     * @return DebugBar
     */
    public function getDebugBar(): DebugBar
    {
        return $this->debugBar;
    }
}
