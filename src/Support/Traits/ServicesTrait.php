<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Support\Traits;

use Affinity4\Dianoia\App\App;
use InvalidArgumentException;
use Laminas\Config\Config;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

trait ServicesTrait
{
    /**
     * Register Configuration
     *
     * @param array $config
     *
     * @return void
     */
    protected function registerConfiguration(array $config = []): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        if (!array_key_exists('app', $config)) {
            throw new InvalidArgumentException("Config array must have top level key 'app'");
        }

        if (!array_key_exists('database', $config)) {
            throw new InvalidArgumentException("Config array must have top level key 'database'");
        }

        $this->getContainer()->set('config.app', new Config($config['app']));
        $this->getContainer()->set('config.database', new Config($config['database']));

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function registerServices(): void
    {
        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.before', $this);

        $this->getContainer()->set(ResponseFactoryInterface::class, new ResponseFactory());
        $this->getContainer()->set(ResponseInterface::class, new Response());

        $this->dispatchEvent(App::class . '::' . __FUNCTION__ . '.after', $this);
    }
}
