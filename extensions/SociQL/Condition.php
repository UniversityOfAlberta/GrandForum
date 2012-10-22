<?php
/**
 * Contain all the information related to conditions in the query
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class Condition {
	
     private $value;
     private $leftId;
     private $leftPropertyRealName;
     private $operator;
     private $leftQuery;
     private $sparqlTriplet;
     private $rightId;
     private $rightPropertyName;
     private $rightPropertyRealName;
     private $rightObjectName;
     private $rightQuery;

     /**
     * Constructs a Condition object
     */
     function __construct() { }

     /**
     * Get the value to the right of the condition, in case that the it is
     * a condition for constant value.  If it's not constant, it returns null
     * @return string Constant value of the condition
     */
     public function getValue() {
            return $this->value;
     }

     /**
     * Set the value to the right of the condition, in case that the it is
     * a condition for constant value.
     * @param string $value Constant value of the condition
     */
     public function setValue($value) {
            $this->value = $value;
     }

     /**
     * Get the property id of the left property in the condition.
     * @return int Property id of the left property
     */
     public function getLeftId() {
            return $this->leftId;
     }

     /**
     * Set the property id of the left property in the condition.
     * @param int $id Property id of the left property
     */
     public function setLeftId($id) {
            $this->leftId = $id;
     }

     /**
     * Get the real name of the left property in the condition.
     * @return string Real name of the left property
     */
     public function getLeftPropertyRealName() {
            return $this->leftPropertyRealName;
     }

     /**
     * Set the real name of the left property in the condition.
     * @param string $name Real name of the left property
     */
     public function setLeftPropertyRealName($name) {
            $this->leftPropertyRealName = $name;
     }

     /**
     * Get the operator in the condition
     * @return string Operator
     */
     public function getOperator() {
            return $this->operator;
     }

     /**
     * Set the operator in the condition
     * @param string $operator Operator
     */
     public function setOperator($operator) {
            $this->operator = $operator;
     }

     /**
     * Get the query of the left property in the condition.
     * @return string Query of the left property
     */
     public function getLeftQuery() {
            return $this->leftQuery;
     }

     /**
     * Set the query of the left property in the condition.
     * @param string $query Query of the left property
     */
     public function setLeftQuery($query) {
            $this->leftQuery = $query;
     }

     /**
     * Get the Sparql triplet from the left property (if it is from Sparql)
     * @return string Sparql triplet
     */
     public function getSparqlTriplet() {
            return $this->sparqlTriplet;
     }

     /**
     * Set the Sparql triplet from the left property
     * @param string $triplet Sparql triplet
     */
     public function setSparqlTriplet($triplet) {
            $this->sparqlTriplet = $triplet;
     }

     /**
     * Get the property id of the right property in the condition.
     * @return int Property id of the right property
     */
     public function getRightId() {
            return $this->rightId;
     }

     /**
     * Set the property id of the right property in the condition.
     * @param int $id Property id of the right property
     */
     public function setRightId($id) {
            $this->rightId = $id;
     }

     /**
     * Get the real name of the right property in the condition.
     * @return string Real name of the right property
     */
     public function getRightPropertyRealName() {
            return $this->rightPropertyRealName;
     }

     /**
     * Set the real name of the right property in the condition.
     * @param string $name Real name of the right property
     */
     public function setRightPropertyRealName($name) {
            $this->rightPropertyRealName = $name;
     }

     /**
     * Get the name of the right property in the condition.
     * @return string Name of the right property
     */
     public function getRightPropertyName() {
            return $this->rightPropertyName;
     }

     /**
     * Set the name of the right property in the condition.
     * @param string $name Name of the right property
     */
     public function setRightPropertyName($name) {
            $this->rightPropertyName = $name;
     }

     /**
     * Get the name of the right object in the condition.
     * @return string Name of the right object
     */
     public function getRightObjectName() {
            return $this->rightObjectName;
     }

     /**
     * Set the name of the right object in the condition.
     * @param string $name Name of the right object
     */
     public function setRightObjectName($name) {
            $this->rightObjectName = $name;
     }

     /**
     * Evaluate if the right id exists.  If it exists, it means that
     * the condition is to a variable (and not to a constant) value.
     * @return boolean True if the right id is set.  False otherwise.
     */
     public function existRightId() {
            if (isset($this->rightId))
                    return true;
            return false;
     }

     /**
     * Get the query of the right property in the condition.
     * @return string Query of the right property
     */
     public function getRightQuery() {
            return $this->rightQuery;
     }

     /**
     * Set the query of the right property in the condition.
     * @param string $query Query of the right property
     */
     public function setRightQuery($query) {
            $this->rightQuery = $query;
     }
}
?>