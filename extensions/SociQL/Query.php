<?php 
/**
 *
 * Structured representation of a query
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */

class Query {

    private static $objects = 	array();
    private static $properties = 	array();
    private static $relations = 	array();
    private static $conditions = 	array();
    private static $orderAlgorithm =null;
    private static $orderObject =   null;
    private static $limit =         0;


    /**
     * Constructs a new Query object
     * @param array $objects Query objects (From)
     * @param array $properties Query properties
     * @param array $relations Query relations
     * @param array $conditions Query conditions
     * @param string $orderAlgorithm Order algorithm (INDEGREE, OUTDEGREE, DEGREE, CLOSENESS, BETWEENNESS, PAGERANK)
     * @param string $orderObject Order object
     * @param int $limit Maximum number of tuples in the result
     */
    function __construct($objects = array(), $properties = array(), $relations = array(),
        $conditions = array(), $orderAlgorithm = null, $orderObject = null, $limit = 0) {
        $this->objects = 	$objects;
        $this->properties = $properties;
        $this->relations = 	$relations;
        $this->conditions = $conditions;
        $this->orderAlgorithm =      $orderAlgorithm;
        $this->orderObject =      $orderObject;
        $this->limit = $limit;

    }


    /**
     * Get object with the specified name
     * @param string $objectName Object name
     * @return Object Object
     */
    public function getObject($objectName) {
        if (isset($this->objects[$objectName])) {
            return $this->objects[$objectName];
        }
    }


    /**
     * Get property with the specified name and object name
     * @param string $objectName Object name
     * @param string $propertyName Property name
     * @return Property Property
     */
    public function getProperty($objectName, $propertyName) {
        if (isset($this->properties[$objectName][$propertyName])) {
            return $this->properties[$objectName][$propertyName];
        }
    }


    /**
     * Get relation with the specified name and property
     * @param string $relationName Object name
     * @param string $relationProperty Property name
     * @return Relation Relation
     */
    public function getRelation($relationName, $relationProperty = "_") {
        if (isset($this->relations[$relationName][$relationProperty])) {
            return $this->relations[$relationName][$relationProperty];
        }
    }


    /**
     * Get condition with the specified object name and property name
     * @param string $objectName Object name
     * @param string $propertyName Property name
     * @return Condition Condition
     */
    public function getCondition($objectName, $propertyName = "_") {
        if (isset($this->conditions[$objectName][$objectProperty])) {
            return $this->conditions[$objecName][$objectProperty];
        }
    }


    /**
     * Get order algorithm (INDEGREE, OUTDEGREE, DEGREE, CLOSENESS, BETWEENNESS, PAGERANK)
     * @return string Order algorithm
     */
    public function getOrderAlgorithm() {
        return $this->orderAlgorithm;
    }


    /**
     * Get ordering object
     * @return string Ordering object
     */
    public function getOrderObject() {
        return $this->orderObject;
    }


    /**
     * Get maximum number of tuples in the result
     * @return int Maximum number of tuples in the result
     */
    public function getLimit() {
        return $this->limit;
    }


    /**
     * Get all query objects
     * @return array Query objects
     */
    public function getAllObjects() {
        return $this->objects;
    }


    /**
     * Get all query properties
     * @return array Query properties
     */
    public function getAllProperties() {
        return $this->properties;
    }


    /**
     * Get all query relations
     * @return array Query relations
     */
    public function getAllRelations() {
        return $this->relations;
    }


    /**
     * Get all query conditions
     * @return array Query conditions
     */
    public function getAllConditions() {
        return $this->conditions;
    }


    /**
     * Add a new relation to the query
     * @param Relation $relation New relation
     */
    public function addRelation($relation) {
        $counter = 0;

        do {
            $counter ++;
        } while (isset($this->relations["rel"]["_"][$counter]));

        $this->relations["rel"]["_"][$counter] = $relation;
    }


    /**
     * Add a new relation to the query with a given name
     * @param string $relName Relation name
     * @param string $propName Relation property name
     * @param string $ref Relation reference
     * @param Relation $relation New relation
     */
    public function addNamedRelation($relName, $propName, $ref, $relation) {
        $this->relations[$relName][$propName][$ref] = $relation;
    }


    /**
     * Add a new object to the query
     * @param Object $object New object
     */
    public function addObject($object) {
        $counter = 0;

        do {
            $counter ++;
        } while (isset($this->objects["obj".$counter]));

        $this->objects["obj".$counter] = $object;

        return "obj".$counter;
    }


    /**
     * Add a new object to the query with a given name
     * @param string $name Object name
     * @param Object $object New object
     */
    public function addNamedObject($name, $object) {
        $this->objects[$name] = $object;
    }


    /**
     * Add a new property to the query with a given name
     * @param string $objName Object name
     * @param string $propName Property name
     * @param Property $property New property
     */
    public function addNamedProperty($objName, $propName, $property) {
        $this->properties[$objName][$propName] = $property;
    }


    /**
     * Add a new condition to the query with a given name
     * @param string $objName Object name
     * @param string $propName Property name
     * @param string $ref Condition reference
     * @param Condition $condition New condition
     */
    public function addNamedCondition($objName, $propName, $ref, $condition) {
        $this->conditions[$objName][$propName][$ref] = $condition;
    }
}
?>