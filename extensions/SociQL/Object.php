<?php
class Object {

    private $id;
    private $type;
    private $query;
    private $siteId;
    private $nameActorId;       //name of actor id
    private $requiredProps;
    private $numberIds;
    private $baseUrl;
    private $siteType;
    private $realName;
    private $ontObjectIds;
    private $ontologyId;
    private $undefObject = false;

    /**
     * Constructs a social object
     * @param string $type Type of object.  It could be <i>NETWORK</i> or <i>ONTOLOGY</i>
     */
    function __construct($type = 'NETWORK') {
        $this->type = $type;
    }

    /**
     * Get object id
     * @return string Object id
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set object id
     * @param string $id Object id
     */
    public function setId($id) {
        $this->id = $id;
    }


    /**
     * Get object type
     * @return string Object type
     */
    public function getType() {
        return $this->type;
    }


    /**
     * Set object query
     * @param string $query Object query
     */
    public function setQuery($query) {
        $this->query = $query;
    }


    /**
     * Get object query
     * @return string Object query
     */
    public function getQuery() {
        return $this->query;
    }


    /**
     * Set object site id
     * @param int $id Object site id
     */
    public function setSiteId($id) {
        $this->siteId = $id;
    }


    /**
     * Get object site id
     * @return int Object site id
     */
    public function getSiteId() {
        return $this->siteId;
    }


    /**
     * Set name of the object id
     * @param string $name Name of the object id
     */
    public function setNameActorId($name) {
        $this->nameActorId = $name;
    }


    /**
     * Get name of the object id
     * @return string Name of the object id
     */
    public function getNameActorId() {
        return $this->nameActorId;
    }


    /**
     * Set id of required properties for significant properties
     * @param array $props id of required properties
     */
    public function setRequiredProps($props) {
        $this->requiredProps = $props;
    }


    /**
     * Get id of required properties for significant properties
     * @return array id of required properties
     */
    public function getRequiredProps() {
        return $this->requiredProps;
    }


    /**
     * Get id of a required property for significant properties
     * @param int $index Index of required property
     * @return int Id of required property
     */
    public function getRequiredProp($index) {
        return $this->requiredProps[$index];
    }


    /**
     * ???
     * @return int ???
     */
    public function setNumberIds($ids) {
        $this->numberIds = $ids;
    }


    /**
     * Get the number of ids???
     * @return int Number of ids
     */
    public function getNumberIds() {
        return $this->numberIds;
    }


    /**
     * Get the base url for significant properties
     * @return string Base url
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }


    /**
     * Set the base url for significant properties
     * @param string $url Base url
     */
    public function setBaseUrl($url) {
        $this->baseUrl = $url;
    }


    /**
     * Get object site type
     * @return string Object site type
     */
    public function getSiteType() {
        return $this->siteType;
    }


    /**
     * Set object site type
     * @param string $type Object site type
     */
    public function setSiteType($type) {
        $this->siteType = $type;
    }


    /**
     * Get object real name
     * @return string Object real name
     */
    public function getRealName() {
        return $this->realName;
    }


    /**
     * Set object real name
     * @param string $name Object real name
     */
    public function setRealName($name) {
        $this->realName = $name;
    }


    /**
     * Get object ids represented by the ontology object
     * @return array object ids represented by the ontology object
     */
    public function getOntologyObjectIds() {
        return $this->ontObjectIds;
    }


    /**
     * Set object ids represented by the ontology object
     * @param array $ids Object ids represented by the ontology object
     */
    public function setOntologyObjectIds($ids) {
        $this->ontObjectIds = $ids;
    }


    /**
     * Get ontology object id
     * @return int Ontology object id
     */
    public function getOntologyId() {
        return $this->ontologyId;
    }


    /**
     * Set ontology object id
     * @param int $id Ontology object id
     */
    public function setOntologyId($id) {
        $this->ontologyId = $id;
    }


    /**
     * Is an object created from an undefined relation
     * @return boolean True if it's from an UNDEF relationship. False, otherwise.
     */
    public function IsUndefObject() {
        return $this->undefObject;
    }


    /**
     * Set boolean indicating if the object created from an undefined relation
     * @param boolean $undef True if it's from an UNDEF relationship. False, otherwise.
     */
    public function setUndefObject($undef) {
        $this->undefObject = $undef;
    }
}
?>