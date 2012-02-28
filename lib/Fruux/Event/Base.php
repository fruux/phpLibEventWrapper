<?php

namespace Fruux\Event;

/**
 * The 'base' class is your main loop.
 *
 * @package Fruux
 * @subpackage Event 
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Base {

    /**
     * This constant tell the event loop to only run 1 iteration.
     */
    const LOOP_ONCE = EVLOOP_ONCE;

    /**
     * This constant tells the event loop to exit if there's no events to 
     * handle.
     */
    const LOOP_NONBLOCK = EVLOOP_NONBLOCK;

    /**
     * eventBase 
     * 
     * @var resource 
     */
    protected $eventBase;

    /**
     * Creates the main event loop
     *
     * @param int $priorities The number of different priorities 
     */
    public function __construct($priorities = null) {

        $this->eventBase = event_base_new();
        if (!is_null($priorities)) {
            event_base_priority_init($this->eventBase, $priorities);
        }

    }

    /**
     * Starts the event loop
     *
     * To only run 1 loop, pass the \Fruux\Event\Base::LOOP_ONCE flag.
     * To make sure that this method does not block if there is nothing to do, 
     * pass the \Fruux\Event\Base::LOOP_NONBLOCK flag.
     *
     * This method returns 0 on success, -1 when an error happened or 1 if 
     * there were not events. 
     *
     * @param int $flags 
     * @return int 
     */
    public function loop($flags = 0) {

        return event_base_loop($this->eventBase, $flags);

    }

    /**
     * Immediately stops the loop, and doesn't handle any further pending 
     * events.
     *
     * Returns true on success or false on error. 
     * 
     * @return bool 
     */
    public function breakLoop() {

        return event_base_loopbreak($this->eventBase);

    }

    /**
     * This method stops the loop as soon as all pending events are handled.
     *
     * If a timeout is given, it will only start this process after the given 
     * microseconds.  
     * 
     * @param int $timeout 
     * @return void
     */
    public function exitLoop($timeout = -1) {

        return event_base_loopexit($this->eventBase);

    }

    /**
     * Adds an event to the loop 
     * 
     * @return void
     */
    public function add(AbstractEvent $event) {

        event_base_set($event->getResource(), $this->eventBase);

    }

    /**
     * Frees up any open resources
     */
    public function __destruct() {

        event_base_free($this->eventBase);
        unset($this->eventBase);

    }

}

?>
