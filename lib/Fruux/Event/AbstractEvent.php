<?php

namespace Fruux\Event;

/**
 * The abstract event is the basis for all events.
 *
 * @package Fruux
 * @subpackage Event
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class AbstractEvent {

    /**
     * Libevent resource
     */
    protected $resource;

    /**
     * Sets the Event Base
     *
     * @param Base $base
     * @return bool
     */
    public function setBase(Base $base) {

        return event_base_set($this->resource, $base->getResource());

    }

    /**
     * Frees the event, cleans up resources
     *
     * @return void
     */
    abstract function free();

    /**
     * Free up resources
     */
    public function __destruct() {

        $this->free();

    }

}
