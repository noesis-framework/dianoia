<?php

declare(strict_types=1);

namespace Noesis\Dianoia\Support\Traits;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ResponseCollection;

trait EventTrait
{
    protected ?EventManagerInterface $eventManager = null;

    /**
     * Set event manager.
     *
     * @param EventManagerInterface $eventManager
     *
     * @return EventTrait
     */
    public function setEventManager(EventManagerInterface $eventManager): self
    {
        $eventManager->setIdentifiers([
            __CLASS__,
            get_called_class()
        ]);
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * Get event manager.
     *
     * If an event manager exists return it, otherwise return a new one.
     *
     * @return EventManagerInterface
     */
    public function getEventManager(): EventManagerInterface
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }

    /**
     * Add event listener.
     *
     * @param string   $eventName
     * @param callable $listener
     * @param          $priority
     *
     * @return callable
     */
    public function addEventListener(string $eventName, callable $listener, $priority = 1): callable
    {
        return $this->getEventManager()->attach($eventName, $listener, $priority);
    }

    /**
     * Dispatch event.
     *
     * @param string $eventName
     * @param object $target
     * @param        $arglist
     *
     * @return ResponseCollection
     */
    public function dispatchEvent(string $eventName, object $target, ...$arglist): ResponseCollection
    {
        return $this->getEventManager()->trigger($eventName, $target, $arglist);
    }
}
