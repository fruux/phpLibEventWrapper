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
abstract class AbstractEvent extends Event {

    protected $resource;

    /**
     * Returns the underlying event resource
     * 
     * @return resource 
     */
    public function getResource() {

        return $this->resource;

    }

}
