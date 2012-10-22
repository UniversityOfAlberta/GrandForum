<?php
/**
 * Contain all the information related to projection attributes
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class ProjectionAttribute {
	 
	private $value;
	private $visible;
	private $objectName;
	private $propertyName;
	private $isId;
	private $preferredName;
        private $significant;

        /**
         * Constructs a new Projection Attribute object
         */
	function __construct() { }


        /**
         * Get SQL value of projection attribute
         * @return string SQL value
         */
	public function getValue() {
		return $this->value;
	}


        /**
         * Set SQL value of projection attribute
         * @param string $value SQL value
         */
	public function setValue($value) {
		$this->value = $value;
	}


        /**
         * Get a boolean value indicating the visibility of the projection attribute
         * @return boolean Visibility
         */
	public function isVisible() {
		return $this->visible;
	}


        /**
         * Set a boolean value indicating the visibility of the projection attribute
         * @param boolean $visible Visibility
         */
	public function setVisibility($visible) {
		$this->visible = $visible;
	}


        /**
         * Get a boolean value indicating if the projection attribute is significant
         * @return boolean Is significant?
         */
        public function isSignificant() {
		return $this->significant;
	}


        /**
         * Set a boolean value indicating if the projection attribute is significant
         * @param boolean $significant Is significant?
         */
	public function setSignificant($significant) {
		$this->significant = $significant;
	}


        /**
         * Get Object name
         * @return string Object name
         */
	public function getObjectName() {
		return $this->objectName;
	}


        /**
         * Set Object name
         * @param string $objectName Object name
         */
	public function setObjectName($objectName) {
		$this->objectName = $objectName;
	}


        /**
         * Get Property name
         * @return string Property name
         */
	public function getPropertyName() {
		return $this->propertyName;
	}


        /**
         * Set Property name
         * @return string Property name
         */
	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
	}


        /**
         * Get a boolean value indicating if the projection attribute is an id
         * @return boolean True if is id, false otherwise
         */
	public function isId() {
		return $this->isId;
	}


        /**
         * Set a boolean value indicating if the projection attribute is an id
         * @param boolean $isId True if is id, false otherwise
         */
	public function setIsId($isId) {
		$this->isId = $isId;
	}


        /**
         * Get alias of projection attribute
         * @return string Alias
         */
	public function getPreferredName() {
		return $this->preferredName;
	}


        /**
         * Set alias of projection attribute
         * @param string $name Alias
         */
	public function setPreferredName($name) {
		$this->preferredName = $name;
	}
}
?>