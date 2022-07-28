<?php

declare(strict_types=1);

use Affinity4\Dianoia\App\App;
use Affinity4\Dianoia\Container\Container;
use Aura\Session\Segment;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Collection;
use Laminas\Config\Config;
use League\Route\Route;
use League\Route\RouteGroup;
use League\Route\Router;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Test\Mock\Middleware;

beforeEach(function () {
    $app = new App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot();
    $this->app = $app;
});

test(App::class . ' accepts super globals and creates valid request', function () {
    $_GET['test'] = 'test.get';
    $_POST['test'] = 'test.post';
    $_COOKIE['test'] = 'test.cookie';
    $_FILES[] = new UploadedFile(tmpfile(), 100, 0, 'tmp');

    $app = new App(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES
    );
    $app->boot();

    expect($app->getRequest() instanceof ServerRequest)->toBeTrue();
    expect(array_keys($app->getRequest()->getServerParams()))->toContain('DOCUMENT_ROOT', 'SCRIPT_NAME', 'SCRIPT_FILENAME');
    expect($app->getRequest()->getQueryParams()['test'])->toBe('test.get');
    expect($app->getRequest()->getParsedBody()['test'])->toBe('test.post');
    expect($app->getRequest()->getCookieParams()['test'])->toBe('test.cookie');
    expect($app->getRequest()->getUploadedFiles()[0] instanceof UploadedFile)->toBeTrue();
});

test(App::class . '::getContainer() method returns instance of Container', function () {
    expect($this->app->getContainer())->toBeInstanceOf(Container::class);
});

test(App::class . ' can get Response instance from Container', function () {
    expect($this->app->getContainer()->get(Response::class) instanceof Response)->toBeTrue();
});

test(App::class . '::getRouter() returns instance of ' . Router::class, function () {
    $router = $this->app->getRouter();

    expect($router)->toBeInstanceOf(Router::class);
});

test(App::class . '::get() creates GET route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->get('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class);
    expect($route->getMethod())->toBe('GET');
    expect($route->getPath())->toBe('/');
});

test(App::class . '::post() creates POST route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->post('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class);
    expect($route->getMethod())->toBe('POST');
    expect($route->getPath())->toBe('/');
});

test(App::class . '::delete() creates DELETE route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->delete('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class)
        ->and($route->getMethod())->toBe('DELETE')
        ->and($route->getPath())->toBe('/');
});

test(App::class . '::put() creates PUT route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->put('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class)
        ->and($route->getMethod())->toBe('PUT')
        ->and($route->getPath())->toBe('/');
});

test(App::class . '::patch() creates PATCH route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->patch('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class)
        ->and($route->getMethod())->toBe('PATCH')
        ->and($route->getPath())->toBe('/');
});

test(App::class . '::options() creates OPTIONS route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->options('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class)
        ->and($route->getMethod())->toBe('OPTIONS')
        ->and($route->getPath())->toBe('/');
});

test(App::class . '::head() creates HEAD route', function () {
    /** @var App $app */
    $app = $this->app;
    $route = $app->head('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['success']);
    });

    expect($route)->toBeInstanceOf(Route::class)
        ->and($route->getMethod())->toBe('HEAD')
        ->and($route->getPath())->toBe('/');
});

test(App::class . '::group() creates a route group', function () {
    /** @var App $app */
    $app = $this->app;
    $routeGroup = $app->group('/admin', function ($group) {
        $group->get('/', function (ServerRequest $request) {
            return new Response\JsonResponse(['data' => 'success']);
        });
    });

    expect($routeGroup)->toBeInstanceOf(RouteGroup::class)
        ->and($routeGroup->getPrefix())->toBe('/admin');
});

test(App::class . '::addMiddleware() adds middleware to stack', function () {
    $this->app->addMiddleware(new Middleware());

    $middlewareStack = $this->app->getRouter()->getMiddlewareStack();

    expect($middlewareStack)->toBeArray()
        ->and(end($middlewareStack))->toBeInstanceOf(Middleware::class);
});

test(App::class . '::addMiddlewares() adds middleware to stack', function () {
    $expectedCount = count($this->app->getRouter()->getMiddlewareStack()) + 2;

    $this->app->addMiddlewares([new Middleware(), new Middleware()]);

    $middlewareStack = $this->app->getRouter()->getMiddlewareStack();

    expect($this->app->getRouter()->getMiddlewareStack())->toBeArray()
        ->and($middlewareStack)->toHaveLength($expectedCount);
});

test('Application middlewares transform response', function () {
    $this->app->addMiddleware(new Middleware());

    $this->app->get('/', function (ServerRequestInterface $request) {
        return new Response\HtmlResponse('success');
    });

    $response = $this->app->getRouter()->dispatch($this->app->getRequest());

    $middlewareStack = $this->app->getRouter()->getMiddlewareStack();

    expect($middlewareStack)->toBeArray()
        ->and(end($middlewareStack))->toBeInstanceOf(Middleware::class)
        ->and($response->getHeader('X-CSRF-TOKEN')[0])->toBe('csrf-token');
});

test(App::class . '::getSession() returns a valid session segment', function () {
    $session = $this->app->getSession();
    $session->set('test', 'success');

    expect($session)->toBeInstanceOf(Segment::class)
        ->and($session->get('test'))->toBe('success');
});

test(App::class . '::addLogger() throws exception if channel already exists', function () {
    $this->app->addLogger('app');
})->throws(\Psr\Log\InvalidArgumentException::class);

test(App::class . '::getLogger() throws exception if channel does not exist', function () {
    $this->app->getLogger('nope');
})->throws(\Psr\Log\InvalidArgumentException::class);

test('Application::setLogChannel() sets log_channel property', function () {
    $this->app->setLogChannel('test');

    expect($this->app->getLogChannel())->toBe('test');
});

test('App channel can be set by event listener before loadLogger method runs', function () {
    $app = new App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->addEventListener(App::class . '::loadAppLogger.before', function ($event) {
        $app = $event->getTarget();
        $app->setLogChannel('test');
    });
    $app->boot();

    expect($app->getLogger('test'))->toBeInstanceOf(Logger::class);
});

test(App::class . '::getGlobalValue returns value from item in $_SERVER array', function () {
    $_GET['test.get'] = 'success';
    $_POST['test.post'] = 'success';
    $_COOKIE['test.cookie'] = 'success';
    $app = new App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

    expect($app->getGlobalValue('server', 'script_filename'))->toBe('vendor/bin/pest')
        ->and($app->getGlobalValue('get', 'test.get'))->toBe('success')
        ->and($app->getGlobalValue('post', 'test.post'))->toBe('success')
        ->and($app->getGlobalValue('cookie', 'test.cookie'))->toBe('success');
});

test(App::class . '::getConfig returns instance of ' . Config::class, function () {
    expect($this->app->getConfig())->toBeInstanceOf(Config::class);
});

test(App::class . ' $config passed to boot method can be access from getConfig()', function () {
    $app = new Affinity4\Dianoia\App\App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot([
        'app' => [
            'result' => [
                'is' => 'success'
            ]
        ],
        'database' => []
    ]);

    expect($app->getConfig('app')->result->is)->toBe('success');
});

test(App::class . ' $config[\'database\'] passed to boot method can be access from getConfig()', function () {
    $app = new Affinity4\Dianoia\App\App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot([
        'app' => [],
        'database' => [
            'test' => [
                'driver' => 'sqlite',
                'database' => ':memory:'
            ]
        ]
    ]);

    expect($app->getConfig('database')->test->driver)->toBe('sqlite');
});

test(App::class . '::loadConfiguration() throws TypeError config is not array', function () {
    $app = new Affinity4\Dianoia\App\App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot([
        'app' => null,
        'database' => null
    ]);
})->throws(TypeError::class);

test(App::class . '::loadConfiguration() throws InvalidArgumentException when config is missing app', function () {
    $app = new Affinity4\Dianoia\App\App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot(['database' => []]);
})->throws(InvalidArgumentException::class);

test(App::class . '::loadConfiguration() throws InvalidArgumentException when config is missing database', function () {
    $app = new Affinity4\Dianoia\App\App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot(['app' => []]);
})->throws(InvalidArgumentException::class);

test(App::class . '::loadEloquent() correctly makes Eloquent Models available', function () {
    $app = new Affinity4\Dianoia\App\App($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
    $app->boot([
        'app' => [],
        'database' => [
            'default' => [
                'driver' => 'sqlite',
                'database' => ':memory:'
            ]
        ]
    ]);

    Illuminate\Database\Capsule\Manager::schema()->create('users', function($table) {
        $table->bigIncrements('id');
        $table->string('username')->unique();
        $table->timestamps();
    });

    eval('class User extends Illuminate\Database\Eloquent\Model
    {
        protected $fillable = [\'username\'];
    }');
    Manager::table('users')->delete();

    User::create([
        'username' => 'test'
    ]);
    $users = User::where('username', 'test')->get();

    expect($users)->toBeInstanceOf(Collection::class)
        ->and($users->count())->toBe(1);
});

test(App::class . '::run() creates response', function () {
    ob_start();
    $this->app->get('/', function (ServerRequest $request) {
        return new Response\JsonResponse(['data' => 'success']);
    });
    $this->app->run();
    $output = ob_get_clean();

    expect($output)->toBe('{"data":"success"}');
});
