<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Support\Traits;

use Affinity4\Dianoia\App\App;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;

trait RouterTrait
{
    /**
     * @var Router $router
     */
    protected Router $router;

    /**
     * Set Router.
     *
     * @param Router $router
     */
    public function setRouter(Router $router): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $this->router = $router;

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Get Router.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Load Router.
     *
     * @return void
     */
    private function loadRouter()
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $strategy = new ApplicationStrategy();
        $strategy->setContainer($this->getContainer());
        $router = new Router();
        $router->setStrategy($strategy);
        $this->setRouter($router);

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }
}