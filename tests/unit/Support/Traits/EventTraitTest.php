<?php

declare(strict_types=1);

use Laminas\EventManager\ResponseCollection;
use Test\Mock\Eventable;
use Laminas\EventManager\EventManager;

/**
 * @throws ReflectionException
 */
function getListenersForEvent(string $event, EventManager $eventManager, $priority = 1): array
{
    $r = new ReflectionProperty($eventManager, 'events');
    $r->setAccessible(true);

    $events = $r->getValue($eventManager);

    $r->setAccessible(false);

    $eventListenerArray = (isset($events[$event])) ? $events[$event] : [];
    $eventListenerPriority = (! empty($eventListenerArray))
        ? (isset($eventListenerArray[$priority])) ? $eventListenerArray[$priority] : []
        : [];
    return (! empty($eventListenerPriority)) ? $eventListenerPriority[0] : [];
}

test('EventManager::trigger should return ResponseCollection', function () {
    $eventable = new Eventable();

    $event = $eventable->getEventManager()->trigger('onSomeEvent', $this);

    expect($event)->toBeInstanceOf(ResponseCollection::class);
});

test('EventTrait::addEventListener should add listener to event', function () {
    $eventable = new Eventable();

    $eventListener = new class
    {
        public function __invoke($event): void
        {
            var_dump($event);
        }
    };

    $eventable->addEventListener('onSomeEvent', $eventListener);
    $eventable->addEventListener('onSomeEvent', function ($event) {
        var_dump($event);
    });

    $events = getListenersForEvent('onSomeEvent', $eventable->getEventManager());

    expect($events)->toBeArray()
        ->and($events[0])->toBeInstanceOf(get_class($eventListener))
        ->and($events[1])->toBeInstanceOf(Closure::class);
});

test('Dispatch should trigger attached listeners', function () {
    $eventable = new Eventable();

    $eventable->setMessage('');

    $eventable->addEventListener('setMessage', [$eventable, 'onMessage']);
    $eventable->dispatchEvent('setMessage', $eventable, 'success');

    expect($eventable->getMessage())->toBe('success');
});
