<?php

declare(strict_types=1);

namespace Affinity4\Dianoia\Support\Traits;

use Aura\Session\Segment;
use Aura\Session\Session;
use Aura\Session\SessionFactory;

trait SessionTrait
{
    /**
     * @var Session $sessionInstance
     */
    protected Session $sessionInstance;

    /**
     * @var Segment $session
     */
    protected Segment $session;

    /**
     * Set Session instance.
     *
     * @param Session $sessionInstance
     */
    public function setSessionInstance(Session $sessionInstance): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);
        $this->sessionInstance = $sessionInstance;
        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * Get Session instance.
     *
     * @return Session
     */
    public function getSessionInstance(): Session
    {
        return $this->sessionInstance;
    }

    /**
     * Set Session segment.
     *
     * @param Segment $session
     */
    public function setSession(Segment $session): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);
        $this->session = $session;
        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }

    /**
     * Get current Session segment.
     *
     * @return Segment
     */
    public function getSession(): Segment
    {
        return $this->session;
    }

    /**
     * Load Session.
     *
     * @return void
     */
    protected function loadSession(): void
    {
        $this->dispatchEvent(__METHOD__ . '.before', $this);

        $this->setSessionInstance((new SessionFactory())->newInstance($this->cookie));
        $this->setSession($this->getSessionInstance()->getSegment(self::class));

        $this->dispatchEvent(__METHOD__ . '.after', $this);
    }
}
