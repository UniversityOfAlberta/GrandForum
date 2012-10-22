<?php
/**
 * Contain all the information related to paths in UNDEF relationships
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class Path {

    private $objectIds = array();
    private $significantProperties = array();
    private $leftPropertyIds = array();
    private $relationNames = array();
    private $relationIds = array();
    private $queries = array();
    private $currentDepth;
    private $oneToOneRelations = array();
    
    /**
     * Constructs a Path object
     */
    function __construct() { }


    /**
     * Get all ids of the participating objects in the path
     * @return array List of object ids
     */
    public function getObjectIds() {
        return $this->objectIds;
    }


    /**
     * Set all ids of the participating objects in the path
     * @param array $objIds List of object ids
     */
    public function setObjectIds($objIds) {
        $this->objectIds = $objIds;
    }


    /**
     * Get the id of the last element in the path
     * @return int Id of the last object in the path
     */
    public function getLastObjectId() {
        return $this->objectIds[sizeof($this->objectIds)-1];
    }


    /**
     * Get object id of a given object in the path
     * @param int $index Index of the object
     * @return int Object id
     */
    public function getObjectId($index) {
        if (isset($this->objectIds[$index])) {
            return $this->objectIds[$index];
        }

        return null;
    }


    /**
     * Add an object id in the path
     * @param int $objId Object id
     */
    public function addObjectId($objId) {
        array_push($this->objectIds, $objId);
    }


    /**
     * Get all left properties id participating in the path
     * @return array List of left properties ids
     */
    public function getLeftPropertyIds() {
        return $this->leftPropertyIds;
    }


    /**
     * Set all left properties id participating in the path
     * @param array $propIds List of left properties ids
     */
    public function setLeftPropertyIds($propIds) {
        $this->leftPropertyIds = $propIds;
    }


    /**
     * Add a left property id in the path
     * @param int $propId Left property id
     */
    public function addLeftPropertyId($propId) {
        array_push($this->leftPropertyIds, $propId);
    }


    /**
     * Get left property id of a given property in the path
     * @param int $index Index of the left property
     * @return int Left property id
     */
    public function getLeftPropertyId($index) {

        if (isset($this->leftPropertyIds[$index])) {
            return $this->leftPropertyIds[$index];
        }

        return null;
    }


    /**
     * Get relation names of the path
     * @return array List of relation names
     */
    public function getRelationNames() {
        return $this->relationNames;
    }


    /**
     * Set relation names of the path
     * @param array $relNames List of relation names
     */
    public function setRelationNames($relNames) {
        $this->relationNames = $relNames;
    }


    /**
     * Add a relation name in the path
     * @param string $relName Relation name
     */
    public function addRelationName($relName) {
        array_push($this->relationNames, $relName);
    }


    /**
     * Get relation ids of the path
     * @return array List of relation ids
     */
    public function getRelationIds() {
        return $this->relationIds;
    }


    /**
     * Set relation ids of the path
     * @param array $relIds List of relation ids
     */
    public function setRelationIds($relIds) {
        $this->relationIds = $relIds;
    }


    /**
     * Add a relation id in the path
     * @param string $relId Relation id
     */
    public function addRelationId($relId) {
        array_push($this->relationIds, $relId);
    }


    /**
     * Get relation id of a given relation of the path
     * @return int Relation id
     */
    public function getRelationId($index) {
        if (isset($this->relationIds[$index])) {
            return $this->relationIds[$index];
        }

        return null;
    }


    /**
     * Get queries of the properties in the path
     * @return array List of queries
     */
    public function getQueries() {
        return $this->queries;
    }


    /**
     * Set queries of the properties in the path
     * @param array $queries List of queries
     */
    public function setQueries($queries) {
        $this->queries = $queries;
    }


    /**
     * Add a query of a property in the path
     * @param string $relId Relation id
     */
    public function addQuery($query) {
        array_push($this->queries, $query);
    }


    /**
     * Get query of a given property of the path
     * @return string Query
     */
    public function getQuery($index) {
        if (isset($this->queries[$index])) {
            return $this->queries[$index];
        }

        return null;
    }


    /**
     * Get current depth of the path
     * @return int Current depth
     */
    public function getCurrentDepth() {
        return $this->currentDepth;
    }


    /**
     * Set current depth of the path
     * @param int $depth Current depth
     */
    public function setCurrentDepth($depth) {
        $this->currentDepth = $depth;
    }


    /**
     * Get a list of the relation ids of one to one relations in the path
     * @return array List of one to one relations
     */
    public function getOneToOneRelations() {
        return $this->oneToOneRelations;
    }


    /**
     * Set a list of the relation ids of one to one relations in the path
     * @param array $relations List of one to one relations
     */
    public function setOneToOnerelations($relations) {
        $this->oneToOneRelations = $relations;
    }


    /**
     * Add a relation id of one to one relations in the path
     * @param int $relation Id of one to one relation
     */
    public function addOneToOneRelation($relation) {
        array_push($this->oneToOneRelations, $relation);
    }
    
    
    public function addSignificantProperty($property) {
        array_push($this->significantProperties, $property);
    }
    
    public function getSignificantProperty($index) {
        if (isset($this->significantProperties[$index])) {
            return $this->significantProperties[$index];
        }

        return null;
    }
}
?>