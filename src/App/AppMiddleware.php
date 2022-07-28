<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\App;

use Affinity4\Dianoia\Container\Container;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AppMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    protected ServerRequest $request;

    /**
     * Set Request.
     *
     * @param ServerRequest $request
     */
    public function setRequest(ServerRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * Get Request.
     *
     * @return ServerRequest
     */
    public function getRequest(): ServerRequest
    {
        return $this->request;
    }

    /**
     * Get Container.
     *
     * @param ServerRequestInterface $request
     *
     * @return Container
     */
    // protected function getContainer(ServerRequestInterface $request): Container
    // {
    //     return $this->getApp($request)->getContainer();
    // }

    /**
     * Get App.
     *
     * @param ServerRequestInterface $request
     *
     * @return App
     */
    protected function getApp(ServerRequestInterface $request): App
    {
        $this->setRequest($request);

        return $this->getRequest()->getAttribute(App::class);
    }

    /**
     * @inheritDoc
     */
    abstract public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
