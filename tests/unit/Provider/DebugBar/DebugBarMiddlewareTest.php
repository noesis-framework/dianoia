<?php

declare(strict_types=1);

use Affinity4\Dianoia\App\App;
use Laminas\Diactoros\Response\HtmlResponse;

test('', function () {
    $app = new App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot();


     $app->get('/', function () {
         $view = <<<VIEW
<!doctype html>
<html lang="en">
<head>
    <title>Test</title>
</head>
<body>
    <h1>Testing debugbar is injected into response</h1>
</body>
</html>
VIEW;
        return new HtmlResponse($view);
    });

    ob_start();
        $app->run();
    $response = ob_get_clean();

    expect($response)->toContain('<script type="text/javascript" src="/vendor/maximebf/debugbar/src/DebugBar/Resources/debugbar.js"></script>');
    expect($response)->toContain('var phpdebugbar = new PhpDebugBar.DebugBar();');
});