<?php
/**
 * Contain all the information related to properties in the query
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class Property {

    private $id;
    private $type;
    private $query;
    private $realName;
    private $sortable;
    private $significant;
    private $visible;
    private $inProjection = true;
    private $preferredName;
    private $ontPropertyId;

    /**
     * Constructs a new property
     * @param string $type Type of property.  It could be <i>NETWORK</i> or <i>ONTOLOGY</i>
     */
    function __construct($type = 'NETWORK') {
        $this->type = $type;
    }


    /**
     * Get property id
     * @return int Property id
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set property id
     * @param int $id Property id
     */
    public function setId($id) {
        return $this->id = $id;
    }


    /**
     * Get property query
     * @return string Property query
     */
    public function getQuery() {
        return $this->query;
    }


    /**
     * Set property query
     * @param string $query Property query
     */
    public function setQuery($query) {
        return $this->query = $query;
    }


    /**
     * Get property real name
     * @return string Property real name
     */
    public function getRealName() {
        return $this->realName;
    }


    /**
     * Set property real name
     * @param string $name Property real name
     */
    public function setRealName($name) {
        return $this->realName = $name;
    }


    /**
     * Get a boolean indicating if the property is sortable
     * @return boolean True if is sortable, false otherwise
     */
    public function isSortable() {
        return $this->sortable;
    }
    
    
    /**
     * Set a boolean indicating if the property is sortable
     * @param boolean $sortable True if is sortable, false otherwise
     */
    public function setSortable($sortable) {
        return $this->sortable = $sortable;
    }
    
    
    /**
     * Get a boolean indicating if the property is significant
     * @return boolean True if is significant, false otherwise
     */
    public function isSignificant() {
        return $this->significant;
    }
    
    
    /**
     * Set a boolean indicating if the property is significant
     * @param boolean $significant True if is significant, false otherwise
     */
    public function setSignificant($significant) {
        return $this->significant = $significant;
    }
    
    
    /**
     * Get a boolean indicating if the property is visible
     * @return boolean True if is visible, false otherwise
     */
    public function isVisible() {
        return $this->visible;
    }
    
    
    /**
     * Set a boolean indicating if the property is visible
     * @param boolean $visible True if is visible, false otherwise
     */
    public function setVisible($visible) {
        return $this->visible = $visible;
    }
    
    
    /**
     * Get a boolean indicating if the property is is included in the projection
     * @return boolean True if is in the projection, false otherwise
     */
    public function isInProjection() {
        return $this->inProjection;
    }
    
    
    /**
     * Set a boolean indicating if the property is is included in the projection
     * @param boolean $projection True if is in the projection, false otherwise
     */
    public function setProjection($projection) {
        return $this->inProjection = $projection;
    }
    
    
    /**
     * Get property alias
     * @return string Property alias
     */
    public function getPreferredName() {
        return $this->preferredName;
    }


    /**
     * Set property alias
     * @return string $prefName Property alias
     */
    public function setPreferredName($prefName) {
        return $this->preferredName = $prefName;
    }


    /**
     * Get id of the ontology property, in case it is an ontology property
     * @return int Ontology property id
     */
    public function getOntologyPropertyId() {
        return $this->ontPropertyId;
    }


    /**
     * Set id of the ontology property, in case it is an ontology property
     * @param int $id Ontology property id
     */
    public function setOntologyPropertyId($id) {
        $this->ontPropertyId = $id;
    }
}
?>