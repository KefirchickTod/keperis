<?php


namespace Src\Observers;


use SplObserver;

class TableRepository implements \SplSubject
{




    private $observers = [];

    public function __construct()
    {
        $this->observers["*"] = [];
    }

    /**
     * @inheritDoc
     */
    public function attach(SplObserver $observer, string $event = "*")
    {
        $this->initEventGroup($event);
        $this->observers[$event][] = $observer;
    }

    private function initEventGroup(string $event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function detach(SplObserver $observer, string $event = "*")
    {
        foreach ($this->getEventObservers($event) as $key => $eventObserver) {
            if ($eventObserver === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    private function getEventObservers(string $event = "*"): array
    {
        $this->initEventGroup($event);
        $group = $this->observers[$event];
        $all = $this->observers["*"];

        return array_merge($group, $all);
    }

    /**
     * @inheritDoc
     */
    public function notify(string $event = "*", $data = null)
    {
        foreach ($this->getEventObservers($event) as $eventObserver) {
            $eventObserver->update($this, $event, $data);
        }
    }
}