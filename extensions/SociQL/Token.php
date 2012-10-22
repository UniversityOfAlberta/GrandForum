<?php
/**
 * Token used in unitary element in lexical analysis
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class Token {

    private $value;
    private $typeOfToken;


    /**
     * Constructs a new Token
     */
    function __construct() {
    }


    /**
     * Set type of token
     * @param string $type Token type
     */
    function setType($type) {
        $this->typeOfToken = $type;
    }


    /**
     * Set token value
     * @param string $value Token value
     */
    function setValue($value) {
        $this->value = $value;
    }


    /**
     * Get type of token
     * @return string Token type
     */
    function getType() {
        return $this->typeOfToken;
    }


    /**
     * Get token value
     * @return string Token value
     */
    function getValue() {
        return $this->value;
    }
}
?>
