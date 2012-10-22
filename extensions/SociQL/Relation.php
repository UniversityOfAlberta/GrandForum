<?php
/**
 * Contain all the information related to relations in the query
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class Relation {

    private $id;
    private $type;

    private $actorName1;
    private $propertyName1;
    private $propertyId1;
    private $actorName2;
    private $propertyName2;
    private $propertyId2;

    private $query;
    private $variableName;
    private $propertyRelName;

    private $variableType;
    private $realPropRelName;
    private $maxPathLength;

    private $ontologyId;
    private $ontRelationIds;


    /**
     * Constructs a Condition object
     * @param string $type Type of object.  It could be <i>NETWORK</i> or <i>ONTOLOGY</i>
     */
    function __construct($type = 'NETWORK') {
        $this->type = $type;
    }


    /**
     * Get relation id
     * @return int Relation id
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set relation id
     * @param int $id Relation id
     */
    public function setId($id) {
        $this->id = $id;
    }


    /**
     * Get relation type
     * @return string Relation type
     */
    public function getType() {
        return $this->type;
    }


    /**
     * Get name of first object
     * @return string Object name
     */
    public function getActorName1() {
        return $this->actorName1;
    }


    /**
     * Set name of first object
     * @param string $name Object name
     */
    public function setActorName1($name) {
        $this->actorName1 = $name;
    }


    /**
     * Set name of one of the objects
     * @param string $name Object name
     * @param int $index Index for the object (1 and 2 are the two only values)
     */
    public function setActorName($name, $index) {
        if ($index == 1) {
            $this->setActorName1($name);
        } else if ($index == 2) {
                $this->setActorName2($name);
            }
    }


    /**
     * Get name of first property
     * @return string Property name
     */
    public function getPropertyName1() {
        return $this->propertyName1;
    }


    /**
     * Set name of first property
     * @param string $name Property name
     */
    public function setPropertyName1($name) {
        $this->propertyName1 = $name;
    }


    /**
     * Set name of one of the properties
     * @param string $name Property name
     * @param int $index Index for the property (1 and 2 are the two only values)
     */
    public function setPropertyName($name, $index) {
        if ($index == 1) {
            $this->setPropertyName1($name);
        } else if ($index == 2) {
                $this->setPropertyName2($name);
            }
    }


    /**
     * Get name of one of the properties
     * @param int $index Index for the property (1 and 2 are the two only values)
     * @return string Property name
     */
    public function getPropertyName($index) {
        if ($index == 1) {
            return $this->getPropertyName1();
        } else if ($index == 2) {
            return $this->getPropertyName1();
        }
    }


    /**
     * Get id of one of the properties
     * @param int $index Index for the property (1 and 2 are the two only values)
     * @return int Property id
     */
    public function getPropertyId($index) {
        if ($index == 1) {
            return $this->propertyId1;
        } else if ($index == 2) {
                return $this->propertyId2;
            }
    }


    /**
     * Get id of first property
     * @param int $name Property id
     */
    public function getPropertyId1() {
        return $this->propertyId1;
    }


    /**
     * Set id of first property
     * @return int $name Property id
     */
    public function setPropertyId1($id) {
        $this->propertyId1 = $id;
    }


    /**
     * Set id of one of the properties
     * @param string $id Property id
     * @param int $index Index for the property (1 and 2 are the two only values)
     * @return int Property id
     */
    public function setPropertyId($id, $index) {
        if ($index == 1) {
            $this->setPropertyId1($id);
        } else if ($index == 2) {
                $this->setPropertyId2($id);
            }
    }


    /**
     * Get name of second object
     * @return string Object name
     */
    public function getActorName2() {
        return $this->actorName2;
    }


    /**
     * Set name of second object
     * @param string $name Object name
     */
    public function setActorName2($name) {
        $this->actorName2 = $name;
    }


    /**
     * Get name of second property
     * @param string $name Property name
     */
    public function getPropertyName2() {
        return $this->propertyName2;
    }


    /**
     * Set name of second property
     * @param string $name Property name
     */
    public function setPropertyName2($name) {
        $this->propertyName2 = $name;
    }


    /**
     * Get id of second property
     * @param int $name Property id
     */
    public function getPropertyId2() {
        return $this->propertyId2;
    }


    /**
     * Set id of second property
     * @return int $name Property id
     */
    public function setPropertyId2($id) {
        $this->propertyId2 = $id;
    }


    /**
     * Get relation query
     * @return string Relation query
     */
    public function getQuery() {
        return $this->query;
    }


    /**
     * Set relation query
     * @param string $query Relation query
     */
    public function setQuery($query) {
        $this->query = $query;
    }


    /**
     * Get variable name (if exists)
     * @return string Variable name
     */
    public function getVariableName() {
        return $this->variableName;
    }


    /**
     * Set variable name
     * @param string $name Variable name
     */
    public function setVariableName($name) {
        $this->variableName = $name;
    }


    /**
     * Get a value indicating if the variable exists
     * @return boolean Value indicating if the variable exists
     */
    public function existVariableName() {
        if (isset($this->variableName))
            return true;
        return false;
    }


    /**
     * Get property relation name
     * @return string Property relation name
     */
    public function getPropertyRelName() {
        return $this->propertyRelname;
    }


    /**
     * Set property relation name
     * @param string $name Property relation name
     */
    public function setPropertyRelName($name) {
        $this->propertyRelName = $name;
    }


    /**
     * Get variable type
     * @return string Variable type
     */
    public function getVariableType() {
        return $this->variableType;
    }


    /**
     * Set variable type
     * @param string $type Variable type
     */
    public function setVariableType($type) {
        $this->variableType = $type;
    }


    /**
     * Get real name of property relation
     * @return string Real name of property relation
     */
    public function getRealPropertyRelName() {
        return $this->realPropRelName;
    }


    /**
     * Set real name of property relation
     * @param string $name Real name of property relation
     */
    public function setRealPropertyRelName($name) {
        $this->realPropRelName = $name;
    }


    /**
     * Get maximum length in UNDEF relationships
     * @return int Maximum path length
     */
    public function getMaxPathLength() {
        return $this->maxPathLength;
    }


    /**
     * Set maximum length in UNDEF relationships
     * @param int $length Maximum path length
     */
    public function setMaxPathLength($length) {
        $this->maxPathLength = $length;
    }


    /**
     * Get all relation ids generated by an ontology relation
     * @return array List of relation ids
     */
    public function getOntologyRelationIds() {
        return $this->ontRelationIds;
    }


    /**
     * Set all relation ids generated by an ontology relation
     * @param array $ids List of relation ids
     */
    public function setOntologyRelationIds($ids) {
        $this->ontRelationIds = $ids;
    }


    /**
     * Get ontology relation id
     * @return int Ontology relation id
     */
    public function getOntologyId() {
        return $this->ontologyId;
    }


    /**
     * Set ontology relation id
     * @param int $id Ontology relation id
     */
    public function setOntologyId($id) {
        $this->ontologyId = $id;
    }
}
?>