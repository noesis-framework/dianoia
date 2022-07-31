<?php

declare(strict_types=1);

namespace Noesis\Dianoia\Provider\DebugBar\Traits;

use Noesis\Dianoia\Provider\DebugBar\DebugBar;

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
