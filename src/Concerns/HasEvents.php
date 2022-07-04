<?php

namespace Xenus\Concerns;

use Xenus\EventDispatcher as Dispatcher;

trait HasEvents
{
    /**
     * The events map
     * @var array
     */
    protected $events = [];

    /**
     * The dispatcher instance
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Dispatch an event
     *
     * @param  string       $event
     * @param  array|object $document
     *
     * @return void
     */
    protected function dispatch(string $event, $document)
    {
        if (isset($this->dispatcher) === false || isset($this->events[$event]) === false) {
            return ;
        }

        $this->dispatcher->dispatch(
            new $this->events[$event]($document)
        );
    }

    /**
     * Dispatch many events
     *
     * @param  array        $events
     * @param  array|object $document
     *
     * @return void
     */
    protected function dispatchMany(array $events, $document)
    {
        if (isset($this->dispatcher) === false) {
            return ;
        }

        foreach ($events as $event) {
            $this->dispatch($event, $document);
        }
    }

    /**
     * Set the event dispatcher
     *
     * @param  Dispatcher $dispatcher
     *
     * @return self
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}
