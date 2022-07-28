<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Support\Traits;

use Affinity4\Dianoia\App\App;
use Affinity4\Dianoia\Container\Container;

trait ContainerTrait
{
    /**
     * @var Container $container
     */
    protected Container $container;

    /**
     * Set container.
     *
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $this->container = $container;

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Get container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}