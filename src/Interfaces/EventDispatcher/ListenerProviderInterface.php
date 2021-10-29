<?php
declare(strict_types=1);

namespace src\Interfaces\EventDispatcher;


/**
 * Mapper from an event to the listeners that are applicable to that event.
 * @link https://github.com/php-fig/event-dispatcher/blob/master/src/ListenerProviderInterface.php
 */
interface ListenerProviderInterface
{

    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable<callable>
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event) : iterable;
}