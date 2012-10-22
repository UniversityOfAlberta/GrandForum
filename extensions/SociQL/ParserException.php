<?php
/**
 * Exception for parser related errors
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class ParserException extends Exception
{
    /**
     * Constructs a Parser Exception
     * @param string $message Exception message
     * @param int $code Exception code
     */
    public function __construct($message, $code = 0) {
    
        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }

    
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";
    }
}
?>