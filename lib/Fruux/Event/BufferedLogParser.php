<?php

namespace Fruux\Event;

/**
 * The buffered log parser is intended to read and parse large logs, such as
 * webserver logs.
 *
 * This parser can be configured to only process the log in batches, so for
 * instance every hit may not result in a database hit.
 *
 * @package Fruux
 * @subpackage Event
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class BufferedLogParser extends AbstractEvent {

    /**
     * This callback is triggered whenever the buffer is full, or a timeout has
     * occured (and there was data to flush).
     *
     * This instance will be passed as the first argument, and the current
     * buffer as the second argument (as a string).
     *
     * @var callable
     */
    public $onFlush;

    /**
     * The inner Buffer event.
     *
     * @var Buffer
     */
    protected $buffer;

    /**
     * Log lines that haven't been parsed yet
     *
     * @var string
     */
    protected $bufferStr = '';

    /**
     * This is the size the current buffer must exceed before a flush is
     * triggered.
     *
     * @var int
     */
    protected $bufferSize;

    /**
     * Creates the log parser.
     *
     * You must pass a open, readable stream.
     *
     * The timeout specifies how long the parser will wait while not
     * receiving data, to automatically do a flush. If this is specified as -1,
     * it will wait indefinitely.
     *
     * @param resource $stream
     * @param int $timeout
     */
    public function __construct($stream, $bufferSize = 65536, $timeout = -1) {

        $this->buffer = new Buffer($stream,Buffer::READ);
        $this->bufferSize = $bufferSize;

        $this->buffer->onRead = array($this,'readLines');
        $this->buffer->onError = function($buffer, $err) {

            die('Buffer error: ' . $err);

        };

        $self = $this;
        $this->buffer->onEOF = function() use ($self) {
            echo "EOF reached\n";
            $self->flush();
        };

        if ($timeout!==-1) {
            $this->buffer->setTimeout($timeout);
        }
        $this->buffer->onTimeout = function($buffer) use ($self) {
            echo "timeout\n";
            $self->flush();

            // Need to re-enable the buffer. We're just using the timeout to
            // force a flush
            $buffer->enable();
        };

    }

    /**
     * This function is triggered whenever there is data to be read.
     *
     * @return void
     */
    public function readLines() {

        $this->bufferStr.=$this->buffer->read(4096);
        if (strlen($this->bufferStr) >= $this->bufferSize) {
            $this->flush();
        }

    }

    /**
     * Returns the underlying event resource
     *
     * @return resource
     */
    public function getResource() {

        return $this->resource;

    }

    /**
     * Sets the Event Base
     *
     * @param Base $base
     * @return bool
     */
    public function setBase(Base $base) {

        $this->buffer->setBase($base);

    }

    /**
     * Flushes the buffer, and calls the onFlush callback.
     *
     * @return void
     */
    public function flush() {

        $newLine = "\n";

        $lastNewLine = strrpos($this->bufferStr,$newLine);

        // There was no data to flush
        if (!$lastNewLine===false) {
            return;
        }

        $leftOverBytes = substr($this->bufferStr, $lastNewLine+1);
        $this->bufferStr = substr($this->bufferStr, 0, $lastNewLine+1);

        if ($this->onFlush) {
            call_user_func($this->onFlush, $this, $this->bufferStr);
        }
        $this->bufferStr = $leftOverBytes;

    }

}
