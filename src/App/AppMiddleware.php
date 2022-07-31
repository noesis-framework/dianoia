<?php

declare(strict_types=1);

namespace Noesis\Dianoia\App;

use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class AppMiddleware implements MiddlewareInterface
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
    abstract public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;
}
