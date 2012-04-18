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

    /**
     * This callback will be called every time there's something to read.
     *
     * An instance of this object is passed as the first argument.
     *
     * @var callable
     */
    public $onRead;

    /**
     * This callback will be triggered every time the stream is ready to be
     * written to.
     *
     * An instance of this object is passed as the first argument to this
     * callback.
     *
     * @var callable
     */
    public $onWrite;

    /**
     * Triggered when the end of the stream has been reached. Feel free to
     * ignore it, if you intend to create tail-like functionality.
     *
     * This callback receives an instance of this object as teh first argument.
     *
     * @var callable
     */
    public $onEOF;

    /**
     * This callback is triggered when the read or write timeout has been reached.
     *
     * This callback received an instance of this object as the first argument.
     * The second argument is either READ or WRITE depending on which
     * timeout was hit.
     *
     * @var callbable
     */
    public $onTimeout;

    /**
     * This callback will be triggered every time an error has occurred.
     *
     * The error code is supplied as the first argument, an instance of this
     * object as the second object.
     *
     * @var callable
     */
    public $onError;

    /**
     * This constant is used for the $operations constructor argument.
     */
    const READ = EV_READ;

    /**
     * This constant is used for the $operations constructor argument.
     */
    const WRITE = EV_WRITE;

    /**
     * Reference to the stream
     *
     * @var resource
     */
    protected $stream;

    /**
     * Reference to the libevent resource
     *
     * @var resource
     */
    protected $operations;

    /**
     * Creates the buffered event
     *
     * @param resource $stream You must pass an open stream
     * @param int $operations Specify self::READ / self::WRITE to handle these
     *                        events.
     */
    public function __construct($stream, $operations) {

        $self = $this;

        $this->resource = event_buffer_new(
            $stream,
            function() use ($self) {
                if ($self->onRead) {
                    call_user_func($self->onRead, $self);
                }
            },
            function() use ($self) {
                if ($self->onWrite) {
                    call_user_func($self->onWrite, $this);
                }
            },
            function($discard, $errorCode) use ($self) {

                if ($errorCode & EVBUFFER_EOF) {

                    if ($self->onEOF)
                        call_user_func($self->onEOF, $self);

                } elseif ($errorCode & EVBUFFER_TIMEOUT) {

                    if ($self->onTimeout)
                        call_user_func($self->onTimeout, $self, $errorCode & ( Buffer::READ || Buffer::WRITE ));


                } elseif ($self->onError) {
                    call_user_func($self->onError, $self, $errorCode);
                }
            }
        );
        $this->operations = $operations;

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
     * This method is called by an EventBase after the event has been added.
     *
     * @return void
     */
    public function enable() {

        event_buffer_enable($this->resource, $this->operations);

    }

    /**
     * Sets the Event Base
     *
     * @param Base $base
     * @return bool
     */
    public function setBase(Base $base) {

        event_buffer_base_set($this->resource, $base->getResource());
        $this->enable();

    }

    /**
     * Sets the read and write timeout
     *
     * If one argument is supplied, it will be used as both the read and write
     * timeout. If two arguments are supplied, the first is the read, the
     * second is the write timeout.
     *
     * @param int $readTimeOut
     * @param int $writeTimeOut
     * @return void
     */
    public function setTimeout($readTimeOut, $writeTimeOut = null) {

        event_buffer_timeout_set($this->resource, $readTimeOut, (is_null($writeTimeOut)?$readTimeOut:$writeTimeOut));

    }

    /**
     * Calling this method will make sure that the onRead callback is only
     * called when the buffersize exceeds the given argument.
     *
     * @param int $bufferSize
     * @return void
     */
    public function setReadBufferSize($bufferSize) {

        event_buffer_watermark_set($this->resource, self::READ, $bufferSize, null);

    }

    /**
     * Frees the event, cleans up resources
     *
     * @return void
     */
    public function free() {

        if (is_null($this->resource)) return null;
        event_buffer_free($this->resource);
        $this->resource = null;

    }

}
