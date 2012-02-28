<?php

namespace Fruux\Event;

/**
 * The Buffer is an event specifically used for reading and writing to streams.
 *
 * Using buffered event you don't need to deal with the I/O manually, instead 
 * it provides input and output buffers that get filled and drained 
 * automatically. 
 *
 * @package Fruux
 * @subpackage Event 
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Buffer extends AbstractEvent {

    protected $stream;
    protected $readCallback;
    protected $writeCallback;
    protected $errorCallback;

    /**
     * Creates the buffered event
     */
    public function __construct($stream, $readCallback = null, $writeCallback = null, $errorCallback = null) {

        $this->resource = event_buffer_new(
            $stream,
            array($this,'onRead'),
            array($this,'onWrite'),
            array($this,'onError')
        );

    }

    /**
     * Reads data from the stream
     * 
     * @param int $size Number of bytes to read 
     * @return string 
     */
    public function read($size) {

        return event_buffer_read($this->resource, $size);

    }

    /**
     * Writes data to the stream
     * 
     * @param string $data
     * @return bool 
     */
    public function write($data) {

        return event_buffer_write($this->resource, $data);

    }

    /**
     * This event is triggered when there is data to be read from the stream. 
     * 
     * @return void 
     */
    public function onRead() {

        $this->readCallback();    

    }

    /**
     * This event is triggered when the stream is ready to have data written to 
     * it
     * 
     * @return void
     */
    public function onWrite() {

        $this->writeCallback();

    }

    /**
     * This error occurs when an error is triggered on the stream
     * 
     * @param resource $discard This argument is not used 
     * @param int $errorCode 
     * @return void
     */
    public function onError($discard, $errorCode) {

        $this->errorCallback($errorCode);

    }

    /**
     * Sets the priority of this event
     *
     * This should somehow correlate with the priority range set in 
     * \Fruux\Event\Base. If you don't set a priority, (priorities/2) is used.
     * 
     * @param int $priority 
     * @return void
     */
    public function setPriority($priority) {

        event_buffer_priority_set($this->resource, $priority);

    }

    /**
     * Frees the event
     * 
     * @return void 
     */
    public function __destruct() {

        event_buffer_free($this->resource);
        unset($this->resource);

    }


}
