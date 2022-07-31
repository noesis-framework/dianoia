<?php

declare(strict_types=1);

use Noesis\Dianoia\Container\Container;
use Noesis\Dianoia\Container\Exception\NotFoundException;
use Laminas\Config\Config;
use Laminas\Di\Injector;
use Laminas\Diactoros\Response;

test(Container::class . '::getConfig return instance of ' . Config::class, function () {
    $container = new Container();

    expect($container->getConfig() instanceof Config)->toBeTrue();
});

test(Container::class . '::getConfig() is correctly setup from php config file', function () {
    $container = new Container();
    $container->setConfig(new Config(include dirname(__DIR__, 2) . '/config/config.php'));

    $envPropertyExists = isset($container->getConfig()->env);
    expect($envPropertyExists)->toBeTrue()
        ->and($container->getConfig()->env)->toBe('test');

    $cachePropertyExists = isset($container->getConfig()->cache);
    expect($cachePropertyExists)->toBeTrue()
        ->and($container->getConfig()->cache instanceof Config)->toBeTrue();

    $containerPropertyExists = ($cachePropertyExists && isset($container->getConfig()->cache->container)) ? true : false;
    expect($containerPropertyExists)->toBeTrue()
        ->and($containerPropertyExists && $container->getConfig()->cache->container instanceof Config)->toBeTrue();

    $compiledPropertyExists = ($containerPropertyExists && isset($container->getConfig()->cache->container->compiled)) ? true : false;
    $root = dirname(__DIR__,2);
    expect($compiledPropertyExists)->toBeTrue()
        ->and($container->getConfig()->cache->container->compiled)->toBe("$root/tmp/cache/container/compiled");
});

test(Container::class . '::getContainer() returns instance of previously set ' . \DI\Container::class, function () {
    $container = new Container();
    $container->setContainer(new \DI\Container());
    expect($container->getContainer())->toBeInstanceOf(Di\Container::class);
});

test(Container::class . '::getInjector() returns instance of ' . Injector::class, function () {
    $container = new Container();
    $container->setInjector(new Injector(null, $container));
    expect($container->getInjector())->toBeInstanceOf(Injector::class);
});

test(Container::class . '::has() method returns true when class exists in container', function () {
    $container = new Container();
    $container->set(Response::class, new Response());

    expect($container->has(Response::class))->toBeTrue();
});

test(Container::class . '::get() method returns instance of class previously set', function () {
    $container = new Container();
    $container->set(Response::class, new Response());

    expect($container->get(Response::class))->toBeInstanceOf(Response::class);
});

test(Container::class . '::get() throws ' . NotFoundException::class, function () {
    $container = new Container();
    $container->get('\A\Fake\Class');
})->throws(NotFoundException::class);
