<?php

namespace Test\Mock;

use Affinity4\Dianoia\Support\Traits\EventTrait;
use Laminas\EventManager\EventInterface;

class Eventable
{
    use EventTrait;

    private string $message = '';

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function onMessage(EventInterface $event)
    {
        $params = $event->getParams();
        $this->setMessage($params[0]);
    }
}
