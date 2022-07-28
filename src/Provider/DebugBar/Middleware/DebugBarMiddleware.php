<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Provider\DebugBar\Middleware;

use Affinity4\Dianoia\App\AppMiddleware;
use DI\DependencyException;
use DI\NotFoundException;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugBarMiddleware extends AppMiddleware
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * Is Response Content-Type html.
     *
     * @return bool
     */
    private function isResponseHtml(): bool
    {
        $header = $this->response->getHeader('Content-Type');
        $contentType = '__UNKNOWN__';
        if (is_array($header) & count($header)) {
            $contentType = $header[0];
        }

        return (str_contains($contentType, 'text/html;'));
    }

    /**
     * Process the middleware request.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws DependencyException|NotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $debugBar = $this->getApp($request)->getDebugbar();
        $this->response = $handler->handle($request);

        if (! $this->isResponseHtml()) {
            return $this->response;
        }

        $renderer = $debugBar->getJavascriptRenderer();

        $contents = $this->response->getBody()->getContents();
        $contents = str_replace("</head>", "{$renderer->renderHead()}</head>", $contents);
        $contents = str_replace("</body>", "{$renderer->render()}</body>", $contents);

        $this->response = $this->response->withBody(new Stream('php://memory', 'w'));
        $this->response->getBody()->write($contents);

        return $this->response;
    }
}
