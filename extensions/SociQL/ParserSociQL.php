<?php
/**
 *
 * Parse a SociQL query
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */

include_once "ParserSelect.php";
include_once "Object.php";
include_once "Relation.php";
include_once "Property.php";
include_once "Condition.php";
include_once "Query.php";
include_once "ExecutionPlanner.php";

class ParserSociQL {

    private static $lexer;
    private static $curToken = null;

    private static $query;
    private static $queryPlanner;

    /**
     * Constructs a SociQl Parser
     * @param string $string SociQL query
     */
    public function __construct($string = null) {

        if (is_string($string)) {
            self::$lexer = new Lexer($string);
        }
    }

    /**
     * Get the current token
     * @return Token Current token
     */
    public static function getCurrentToken() {
        return self::$curToken;
    }

    /**
     * Get query objects
     * @return array Query objects
     */
    public static function getQueryObjects() {

        if (self::$query != null) {
            return self::$query->getAllObjects();
        }

        return null;
    }

    /**
     * Get query properties
     * @return array Query properties
     */
    public static function getQueryProperties() {

        if (self::$query != null) {
            return self::$query->getAllProperties();
        }

        return null;
    }

    /**
     * Get query relations
     * @return array Query relations
     */
    public static function getQueryRelations() {

        if (self::$query != null) {
            return self::$query->getAllRelations();
        }

        return null;
    }

    /**
     * Get query order algorithm
     * @return string Query order algorithm
     */
    public static function getQueryOrderAlgorithm() {

        if (self::$query != null) {
            return self::$query->getOrderAlgorithm();
        }

        return null;
    }

    /**
     * Get query order object
     * @return string Query order object
     */
    public static function getQueryOrderObject() {

        if (self::$query != null) {
            return self::$query->getOrderObject();
        }

        return null;
    }


    /**
     * Converts a tree representation of a query into a string
     * @param string $options Options for the result query. <i>NO-CONDITIONS</i>
     * indicates that the resulting string should not contain conditions
     * @return string SociQL query (string)
     */
    public static function queryToString($options = 'NO-CONDITIONS') {

        include "db.inc.php";

        $properties = 	self::$query->getAllProperties();
        $objects = 	self::$query->getAllObjects();
        $conditions = 	self::$query->getAllConditions();
        $relations = 	self::$query->getAllRelations();

        $sociqlQuery = "SELECT ";

        foreach ($properties as $objectName=>$propGroup) {
            foreach ($propGroup as $propertyName=>$property) {
                $sociqlQuery .= $objectName . "." . $propertyName . ", ";
            }
        }

        $sociqlQuery = substr($sociqlQuery, 0, strlen($sociqlQuery)-2);
        $sociqlQuery .= " FROM ";

        foreach ($objects as $objectName=>$object) {
            if ($object->getType() == 'ONTOLOGY') {
                $sociqlQuery .= "ONT.";

                $sql_query =    "SELECT name
                                    FROM sociql_ontology_ent
                                    WHERE id = " . $object->getOntologyId();

                $sql_result = DB::query($sql_query, $conn);

                if ($row = DB::fetchAssoc($sql_result)) {
                    $sociqlQuery .= $row['name'] . " " . $objectName . ", ";
                }
            } else {
                $sql_query =    "SELECT name
                                        FROM sociql_actor
                                        WHERE id = " . $object->getId();

                $sql_result = DB::query($sql_query, $conn);

                if ($row = DB::fetchAssoc($sql_result)) {
                    $sociqlQuery .= $row['name'] . " " . $objectName . ", ";
                }
            }
        }

        $sociqlQuery = substr($sociqlQuery, 0, strlen($sociqlQuery)-2);
        $sociqlQuery .= " WHERE ";

        foreach ($relations as $relationName=>$relGroup) {
            foreach ($relGroup as $relationPropName=>$relSubgroup) {
                foreach ($relSubgroup as $relationRef=>$relation) {

                    if ($relation->getType() == 'ONTOLOGY') {
                        $sociqlQuery .= "ONT.";

                        $sql_query =    "SELECT name
                                            FROM sociql_ontology_rel
                                            WHERE id = " . $relation->getOntologyId();

                        $sql_result = DB::query($sql_query, $conn);

                        if ($row = DB::fetchAssoc($sql_result)) {
                            $sociqlQuery .= $row['name'] . "(" . $relation->getActorName1() . ", ". $relation->getActorName2() . ") AND ";
                        }
                    } else if ($relation->getType() == 'NETWORK') {

                        $sql_query =    "SELECT name
                                                FROM sociql_relation
                                                WHERE id = " . $relation->getId();

                        $sql_result = DB::query($sql_query, $conn);

                        if ($row = DB::fetchAssoc($sql_result)) {
                            $sociqlQuery .= $row['name'] . "(" . $relation->getActorName1() . ", ". $relation->getActorName2() . ") AND ";
                        }
                    } else if ($relation->getType() == 'UNDEF') {
                        //UNDEF
                        $sociqlQuery .= "UNDEF(" . $relation->getActorName1() . ", ". $relation->getActorName2() . ", " . $relation->getMaxPathLength() . ") AND ";
                    }
                }
            }
        }

        $sociqlQuery = substr($sociqlQuery, 0, strlen($sociqlQuery)-4);

        return $sociqlQuery;
    }


    /**
     * Parses order clause
     * @return array Tree representation of order clause
     */
    public function parseOrderClause() {

        $clause = array();

        //self::getNextToken();

        if (self::getCurrentToken() != null) {
            if (self::getCurrentToken()->getType() == 'GLOBAL_RES_WORD' && self::getCurrentToken()->getValue() == 'ORDER') {
                self::getNextToken();

                if (self::getCurrentToken()->getType() == 'GLOBAL_RES_WORD' && self::getCurrentToken()->getValue() == 'BY') {

                    self::getNextToken();

                    if (self::getCurrentToken()->getType() == 'ORDER_CRITERIA') {

                        $clause['OrderAlgorithm'] = self::getCurrentToken()->getValue();

                        self::getNextToken();

                        if (self::getCurrentToken()->getType() == 'IDENTIFIER') {
                            $clause['OrderObject'] = self::getCurrentToken()->getValue();
                        } else {
                            return self::raiseError('Expected Object Name');
                        }

                        self::getNextToken();
                    } else {
                        return self::raiseError('Expected Order Criteria');
                    }

                } else {
                    return self::raiseError('Expected \'BY\'');
                }
            }
        }

        return $clause;
    }


    /**
     * Parses properties in the projection
     * @return array Tree representation properties in the projection
     */
    public function parseColumns(array $tree) {
        if (self::$curToken->getType() == "IDENTIFIER") {
            $count = 0;

            while (self::$curToken->getType() != "GLOBAL_RES_WORD" && self::$curToken->getValue() != "FROM" && $count<30) {
                $count++;
                $prevToken = self::$curToken;

                if (self::$curToken->getType() == "IDENTIFIER") {

                    self::getNextToken();

                    if (self::getCurrentToken()->getValue() == '.') {
                        $columnObject = $prevToken->getValue();

                        self::getNextToken();
                        $prevToken = self::getCurrentToken();

                        if (self::getCurrentToken()->getType() == "IDENTIFIER") {
                            $columnName = self::getCurrentToken()->getValue();

                            self::getNextToken();
                            $prevToken = self::getCurrentToken();

                            if (self::getCurrentToken()->getValue() == "AS") {

                                self::getNextToken();
                                $prevToken = self::getCurrentToken();

                                if (self::getCurrentToken()->getType() == "IDENTIFIER") {
                                    $columnAlias = self::getCurrentToken()->getValue();

                                    self::getNextToken();
                                } else {
                                    return self::raiseError('Expected property alias');
                                }
                            } else {
                                $columnAlias = '';
                            }

                            $tree['ColumnObjects'][] = $columnObject;
                            $tree['ColumnProperties'][] = $columnName;
                            $tree['ColumnAliases'][] = $columnAlias;

                            if (self::getCurrentToken()->getValue() == ',') {
                                self::getNextToken();
                            }

                        } else {
                            return self::raiseError('Expected property name');
                        }
                    } else {
                        return self::raiseError('Expected object delimiter');
                    }
                }
            }
        }

        return $tree;
    }


    /**
     * Parse conditional statements into array
     * @return array Tree representation of conditions and relations
     */
    public static function parseSearchClause() {

        $clause = array();

        self::getNextToken();

        while (self::getCurrentToken() != null && (self::getCurrentToken()->getType() == "IDENTIFIER" ||
            (self::getCurrentToken()->getType() == "GLOBAL_RES_WORD" && self::getCurrentToken()->getValue() != "ORDER" && self::getCurrentToken()->getValue() != "LIMIT"))) {

            if (self::getCurrentToken()->getType() == "IDENTIFIER" ||
                (self::getCurrentToken()->getType() == "GLOBAL_RES_WORD" && self::getCurrentToken()->getValue() == "ONT")) {

                $isOntology = false;

                if (self::getCurrentToken()->getType() == "GLOBAL_RES_WORD" && self::getCurrentToken()->getValue() == "ONT") {
                    $isOntology = true;

                    self::getNextToken();

                    if (!(self::getCurrentToken()->getType() == 'SEPARATION_MARK' && self::getCurrentToken()->getValue() == '.')) {
                        return self::raiseError('Expected ontology delimiter');
                    }

                    self::getNextToken();
                }

                $firstOper = self::getCurrentToken();

                self::getNextToken();

                if (self::getCurrentToken()->getValue() == "." && $isOntology == false) {

                    self::getNextToken();

                    if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                        $firstOperProp = self::getCurrentToken();

                        self::getNextToken();

                        if (self::getCurrentToken()->getType() == "RES_SYMBOL") {
                        //Condition
                            $clause['Left']['Type'][] = 'Variable';
                            $clause['Left']['Value']['ObjectName'][] = $firstOper->getValue();
                            $clause['Left']['Value']['PropertyName'][] = $firstOperProp->getValue();
                            $clause['Left']['Value'][] = null;
                            $clause['Oper'][] = self::getCurrentToken()->getValue();

                            self::getNextToken();

                            if (self::getCurrentToken()->getType() == "IDENTIFIER") {
                            //Dynamic condition    e.g.: a1.name = f1.firstName
                                $secondOper = self::getCurrentToken();

                                self::getNextToken();

                                if (self::getCurrentToken()->getValue() == ".") {

                                    self::getNextToken();

                                    if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                                        $clause['Right']['Type'][] = 'Variable';
                                        $clause['Right']['Value']['ObjectName'][] = $secondOper->getValue();
                                        $clause['Right']['Value']['PropertyName'][] = self::getCurrentToken()->getValue();
                                        $clause['Right']['Value'][] = null;

                                        self::getNextToken();

                                    } else {
                                        return self::raiseError('Expected identifier');
                                    }

                                } else {
                                    return self::raiseError('Construct separator');
                                }
                            } else if (self::getCurrentToken()->getType() == "NUMERIC" || self::getCurrentToken()->getType() == "STRING") {
                                //Static Condition    e.g.: a1.name = "Diego"

                                    $clause['Right']['Type'][] = 'Constant';
                                    $clause['Right']['Value'][] = self::getCurrentToken()->getValue();
                                    $clause['Right']['Value']['ObjectName'][] = null;
                                    $clause['Right']['Value']['PropertyName'][] = null;

                                    self::getNextToken();

                                } else {
                                    return self::raiseError('Expected object identifier or constant value');
                                }

                        } else if (self::getCurrentToken()->getValue() == "(") {
                            //Property of Relation  e.g.: Affiliation.since(r1, o1, y)
                                $clause['Relation']['Name'][] = $firstOper->getValue();
                                $clause['Relation']['Property'][] = $firstOperProp->getValue();
                                $clause['Relation']['Type'][] = 'NETWORK';

                                self::getNextToken();

                                if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                                    $clause['Relation']['Object1'][] = self::getCurrentToken()->getValue();

                                    self::getNextToken();

                                    if (self::getCurrentToken()->getValue() == ",") {

                                        self::getNextToken();

                                        if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                                            $clause['Relation']['Object2'][] = self::getCurrentToken()->getValue();

                                            self::getNextToken();

                                            if (self::getCurrentToken()->getValue() == ",") {

                                                self::getNextToken();

                                                if (self::getCurrentToken()->getType() == "IDENTIFIER") {
                                                    $clause['Relation']['Variable'][] = self::getCurrentToken()->getValue();
                                                    $clause['Relation']['MaxDepth'][] = null;

                                                    self::getNextToken();

                                                    if (self::getCurrentToken()->getValue() == ")") {
                                                        self::getNextToken();
                                                    } else {
                                                        return self::raiseError('Expected closing parenthesis');
                                                    }
                                                } else {
                                                    return self::raiseError('ExpectedXX identifier');
                                                }
                                            } else {
                                                return self::raiseError('Expected second property separator');
                                            }
                                        } else {
                                            return self::raiseError('Expected identifier');
                                        }
                                    } else {
                                        return self::raiseError('Expected first property separator');
                                    }
                                }
                            } else {
                                return self::raiseError('Construct not recognized');
                            }

                    } else {
                        return self::raiseError('Expected object identifier');
                    }

                } else if (self::getCurrentToken()->getValue() == "(") {
                    //Relation  e.g.: Affiliation(r1, o1)

                        $clause['Relation']['Name'][] = $firstOper->getValue();
                        $clause['Relation']['Property'][] = null;

                        if ($isOntology) {
                            $clause['Relation']['Type'][] = 'ONTOLOGY';
                        } else {
                            $clause['Relation']['Type'][] = 'NETWORK';
                        }

                        self::getNextToken();

                        if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                            $clause['Relation']['Object1'][] = self::getCurrentToken()->getValue();

                            self::getNextToken();

                            if (self::getCurrentToken()->getValue() == ",") {

                                self::getNextToken();

                                if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                                    $clause['Relation']['Object2'][] = self::getCurrentToken()->getValue();
                                    $clause['Relation']['Variable'][] = null;
                                    $clause['Relation']['MaxDepth'][] = null;

                                    self::getNextToken();

                                    if (self::getCurrentToken()->getValue() == ")") {
                                        self::getNextToken();
                                    } else {
                                        return self::raiseError('Expected closing parenthesis');
                                    }
                                } else {
                                    return self::raiseError('Expected identifier');
                                }
                            } else {
                                return self::raiseError('Expected property separator');
                            }
                        } else {
                            return self::raiseError('Expected identifier');
                        }
                    } else if (self::getCurrentToken()->getType() == "RES_SYMBOL" && $isOntology == false) {

                            $clause['Left']['Type'][] = 'RelationVariable';
                            $clause['Left']['Value'][] = $firstOper->getValue();
                            $clause['Left']['Value']['ObjectName'][] = null;
                            $clause['Left']['Value']['PropertyName'][] = null;
                            $clause['Oper'][] = self::getCurrentToken()->getValue();

                            self::getNextToken();

                            if (self::getCurrentToken()->getType() == "NUMERIC" || self::getCurrentToken()->getType() == "STRING") {
                                $clause['Right']['Type'][] = 'Constant';
                                $clause['Right']['Value'][] = self::getCurrentToken()->getValue();
                                $clause['Right']['Value']['ObjectName'][] = null;
                                $clause['Right']['Value']['PropertyName'][] = null;

                                self::getNextToken();
                            }
                        } else {
                            return self::raiseError('Unexpected token');
                        }

            } else if (self::getCurrentToken()->getType() == "GLOBAL_RES_WORD" && self::getCurrentToken()->getValue() == "UNDEF") {
                //UNDEF Relation  e.g.: UNDEF(r1, o1, #)
                    $firstOper = self::getCurrentToken();

                    self::getNextToken();

                    if (self::getCurrentToken()->getValue() == "(") {

                        $clause['Relation']['Name'][] = $firstOper->getValue();
                        $clause['Relation']['Property'][] = null;
                        $clause['Relation']['Type'][] = 'NETWORK';

                        self::getNextToken();

                        if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                            $clause['Relation']['Object1'][] = self::getCurrentToken()->getValue();

                            self::getNextToken();

                            if (self::getCurrentToken()->getValue() == ",") {

                                self::getNextToken();

                                if (self::getCurrentToken()->getType() == "IDENTIFIER") {

                                    $clause['Relation']['Object2'][] = self::getCurrentToken()->getValue();

                                    self::getNextToken();

                                    if (self::getCurrentToken()->getValue() == ",") {

                                        self::getNextToken();

                                        if (self::getCurrentToken()->getType() == "NUMERIC") {
                                            $clause['Relation']['MaxDepth'][] = self::getCurrentToken()->getValue();

                                            self::getNextToken();

                                            if (self::getCurrentToken()->getValue() == ")") {
                                                self::getNextToken();
                                            } else {
                                                return self::raiseError('Expected closing parenthesis');
                                            }
                                        } else {
                                            return self::raiseError('Expected numeric token');
                                        }
                                    } else {
                                        return self::raiseError('Expected second property separator');
                                    }
                                } else {
                                    return self::raiseError('Expected identifier');
                                }
                            } else {
                                return self::raiseError('Expected first property separator');
                            }
                        }
                    } else {
                        return self::raiseError('Expected first property separator');
                    }
                } else {
                    return self::raiseError('Expected object or relation identifier');
                }

            if (self::getCurrentToken() != null && self::getCurrentToken()->getValue() == 'AND') {
                self::getNextToken();

            }
        }

        return $clause;
    }


    /**
     * Parse SociQL query
     * @return string SQL query representing the original SociQL query
     */
    public function parse() {
        // get action
        self::$curToken = self::$lexer->getNextToken();

        if (self::$curToken != null) {
            if (self::$curToken->getType() == "GLOBAL_RES_WORD" && self::$curToken->getValue() == "SELECT") {
                $queryTree = ParserSelect::parse();
                //print_r($queryTree);
                $this->loadQueryInfo($queryTree);

                //FALTA CHECAR REQUIRED FIELDS
                //FALTA CHECAR QUE ESTEN CONECTADOS

                $this->queryPlanner = new ExecutionPlanner();
                $sqlQuery = $this->queryPlanner->execute(self::$query);

                return $sqlQuery;

            } else {
                return self::raiseError('Unknown action');
            }
        } else {
            return self::raiseError('Nothing to do');
        }
    }


    /**
     * Get projection attributes
     * @return array Projection attributes
     */
    public function getProjectionAttributes() {
        return $this->queryPlanner->getProjectionAttributes();
    }


    /**
     * Load model information about the elements in the query.  Updates the Query object
     * with the corresponding information.
     * @param array $tree Tree representation of the query
     */
    public function loadQueryInfo($tree) {

        include "db.inc.php";

        $objects = array();
        $properties = array();
        $relations = array();
        $conditions = array();
        $orderAlgorithm = null;
        $orderObject = null;
        $limit = 0;

        //Objects in the FROM
        for ($i=0; $i<sizeof($tree['ObjectNames']); $i++) {

            $objectName = $tree['ObjectNames'][$i];
            $objectAlias = $tree['ObjectAliases'][$i];
            $objectType = $tree['ObjectTypes'][$i];

            if ($objectType == 'NETWORK') {
                $sql_query = 	"SELECT sociql_actor.id, query, site_fk, actor_id, url_required_prop, url, type, sociql_actor.real_name
								FROM sociql_actor, sociql_site
								WHERE sociql_actor.site_fk = sociql_site.id AND
									sociql_actor.name = '$objectName'";

                $sql_result = DB::query($sql_query, $conn);

                if ($row = DB::fetchAssoc($sql_result)) {

                    $objects[$objectAlias] = new Object('NETWORK');

                    $objects[$objectAlias]->setId($row["id"]);
                    $objects[$objectAlias]->setQuery($row["query"]);
                    $objects[$objectAlias]->setSiteId($row["site_fk"]);
                    $objects[$objectAlias]->setNameActorId($row["actor_id"]);
                    $objects[$objectAlias]->setRequiredProps(explode(",", $row["url_required_prop"]));
                    $objects[$objectAlias]->setNumberIds(0);
                    $objects[$objectAlias]->setBaseUrl($row["url"]);
                    $objects[$objectAlias]->setSiteType($row["type"]);

                    if ($row["real_name"] != "") {
                        $objects[$objectAlias]->setRealName($row["real_name"]);
                    }
                    else {
                        $objects[$objectAlias]->setRealName($objectName);
                    }
                }
                else {
                    return self::raiseValidationError('Actor ' . $objectName . ' does not exist in the model');
                }

            } else if ($objectType == 'ONTOLOGY') {

                    $objects[$objectAlias] = new Object('ONTOLOGY');

                    $sql_query = 	"SELECT id, level
								FROM sociql_ontology_ent
								WHERE name = '$objectName'";

                    $sql_result = DB::query($sql_query, $conn);

                    if ($row = DB::fetchAssoc($sql_result)) {

                        $entityId = $row["id"];
                        $level = $row["level"];
                        $objects[$objectAlias]->setOntologyId($entityId);

                        $queueEntities = array($entityId);
                        $entities = array();

                        //gets all the upper entity level
                        while (sizeof($queueEntities) > 0) {

                            $curEntity = array_shift($queueEntities);
                            array_push($entities, $curEntity);

                            $sql_query = 	"SELECT from_entity
										FROM sociql_ontology_rel
										WHERE to_entity = $curEntity";

                            $sql_result = DB::query($sql_query, $conn);

                            while ($row = DB::fetchAssoc($sql_result)) {
                                if (!(in_array($row["from_entity"], $entities))) {
                                    array_push($queueEntities, $row["from_entity"]);
                                }
                            }
                        }

                        //iterate until get the lower level entities
                        for (; $level>1; $level--) {

                            $sql_query = 	"SELECT id
										FROM sociql_ontology_ent
										WHERE ";
                            for ($j=0; $j<sizeof($entities); $j++) {
                                $sql_query .= "upper_entity = " . $entities[$j] . " OR ";
                            }
                            $sql_query = substr($sql_query, 0, strlen($sql_query)-4);

                            $sql_result = DB::query($sql_query, $conn);

                            $entities = array();

                            while ($row = DB::fetchAssoc($sql_result)) {
                                array_push($entities, $row["id"]);
                            }
                        }

                        //get the actors from the model
                        $sql_query = 	"SELECT id
									FROM sociql_actor
									WHERE ";
                        for ($j=0; $j<sizeof($entities); $j++) {
                            $sql_query .= "ont_entity = " . $entities[$j] . " OR ";
                        }
                        $sql_query = substr($sql_query, 0, strlen($sql_query)-4);

                        $sql_result = DB::query($sql_query, $conn);

                        $entities = array();

                        while ($row = DB::fetchAssoc($sql_result)) {
                            array_push($entities, $row["id"]);
                        }

                        if (sizeof($entities) > 0) {
                            $objects[$objectAlias]->setOntologyObjectIds($entities);
                        } else {
                            return self::raiseValidationError('Actor ' . $objectName . ' does not have any instance in the model');
                        }

                    } else {
                        return self::raiseValidationError('Actor ' . $objectName . ' does not exist in the ontology model');
                    }

                }
        }


        //Properties in projection
        for ($i=0; $i<sizeof($tree['ColumnObjects']); $i++) {

            $objectName = $tree['ColumnObjects'][$i];
            $propertyName = $tree['ColumnProperties'][$i];
            $propertyAlias = $tree['ColumnAliases'][$i];

            //check if the actor exists
            if (isset($objects[$objectName])) {
                if ($objects[$objectName]->getType() == 'NETWORK') {
                //validate if property exists
                    $sql_query = 	"SELECT id, query, real_name, sortable, significant, queriable
									FROM sociql_property 
									WHERE actor_fk = " . $objects[$objectName]->getId() . " AND name = '$propertyName'";
                    $sql_result = DB::query($sql_query, $conn);

                    if ($row = DB::fetchAssoc($sql_result)) {

                        if ($row["queriable"] == 1) {

                            $properties[$objectName][$propertyName] = new Property();

                            $properties[$objectName][$propertyName]->setId($row["id"]);
                            $properties[$objectName][$propertyName]->setQuery($row["query"]);
                            $properties[$objectName][$propertyName]->setRealName($row["real_name"]);
                            $properties[$objectName][$propertyName]->setSortable(self::numToBoolean($row["sortable"]));
                            $properties[$objectName][$propertyName]->setSignificant(self::numToBoolean($row["significant"]));
                            $properties[$objectName][$propertyName]->setVisible(true);
                            $properties[$objectName][$propertyName]->setPreferredName($propertyAlias);

                            //check the properties required for significant select properties (for url)
                            if ($properties[$objectName][$propertyName]->isSignificant()) {

                            //if it has some additional properties for the url (properties besides id)
                                if (sizeof($objects[$objectName]->getRequiredProps()) > 0) {

                                    $requiredProps = $objects[$objectName]->getRequiredProps();

                                    //look for all the required props for url
                                    for ($j=0; $j<sizeof($requiredProps); $j++) {

                                        $additionalProperty = trim($requiredProps[$j]);

                                        //if the property has not been requested, then include it (but invisible)
                                        if ($additionalProperty != "" && !(isset($properties[$objectName][$additionalProperty]))) {

                                        //validate if property exists
                                            $sql_query = 	"SELECT id, query, real_name, sortable, significant
															FROM sociql_property 
															WHERE actor_fk = " . $objects[$objectName]->getId() . " AND name = '$additionalProperty'";
                                            $sql_result2 = DB::query($sql_query, $conn);

                                            if ($row2 = DB::fetchAssoc($sql_result2)) {

                                                $properties[$objectName][$additionalProperty] = new Property();

                                                $properties[$objectName][$additionalProperty]->setId($row2["id"]);
                                                $properties[$objectName][$additionalProperty]->setQuery($row2["query"]);
                                                $properties[$objectName][$additionalProperty]->setRealName($row2["real_name"]);
                                                $properties[$objectName][$additionalProperty]->setSortable(self::numToBoolean($row2["sortable"]));
                                                $properties[$objectName][$additionalProperty]->setSignificant(self::numToBoolean($row2["significant"]));
                                                $properties[$objectName][$additionalProperty]->setVisible(false);

                                            } else {
                                                return self::raiseValidationError("Property $objectName . $additionalProperty does not exist in the model and is required for url");
                                            }
                                        }
                                    }
                                }
                            }

                        }
                        else {
                            return self::raiseValidationError("Property $objectName . $propertyName can not be queried");
                        }

                    } else {

                        return self::raiseValidationError("Property $objectName . $propertyName does not exist in the model");
                    }

                } else if ($objects[$objectName]->getType() == 'ONTOLOGY') {

                        $properties[$objectName][$propertyName] = new Property('ONTOLOGY');

                        $propertyId = $this->getOntologyPropertyId($objects[$objectName]->getOntologyId(), $propertyName, $conn);
                        $properties[$objectName][$propertyName]->setOntologyPropertyId($propertyId);

                        if ($propertyAlias != '') {
                            $properties[$objectName][$propertyName]->setPreferredName($propertyAlias);
                        } else {
                            $properties[$objectName][$propertyName]->setPreferredName($propertyName);
                        }

                        if ($propertyId === null) {
                            return self::raiseValidationError("Property $propertyName does not exist in the model");
                        }

                    }

            } else {
                return self::raiseValidationError("Object $objectName does not exist");
            }
        }



        //Relations in WHERE
        if (isset($tree['Relation']['Name'])) {
            for ($i=0; $i<sizeof($tree['Relation']['Name']); $i++) {

                if ($tree['Relation']['Name'][$i] != 'UNDEF') {

                    $relationName = 	$tree['Relation']['Name'][$i];
                    $firstObject = 		$tree['Relation']['Object1'][$i];
                    $secondObject = 	$tree['Relation']['Object2'][$i];
                    $relationProperty = $tree['Relation']['Property'][$i];
                    $relationVariable = $tree['Relation']['Variable'][$i];
                    $relationType =		$tree['Relation']['Type'][$i];

                    if ($relationType == 'NETWORK') {
                        $sql_query = 	"SELECT id, property1_fk, real_name1, property2_fk, real_name2, query
										FROM sociql_relation 
										WHERE name = '$relationName' AND 
											((property1_fk IN (SELECT id FROM sociql_property WHERE actor_fk = ".$objects[$firstObject]->getId().") AND 
											  property2_fk IN (SELECT id FROM sociql_property WHERE actor_fk = ".$objects[$secondObject]->getId().")) OR
											 (property2_fk IN (SELECT id FROM sociql_property WHERE actor_fk = ".$objects[$firstObject]->getId().") AND 
											  property1_fk IN (SELECT id FROM sociql_property WHERE actor_fk = ".$objects[$secondObject]->getId()."))) ";

                        $sql_result = DB::query($sql_query, $conn);

                        if ($row = DB::fetchAssoc($sql_result)) {
                            $relationId = 	$row["id"];
                            $prop[0] = 		$row["property1_fk"];
                            $nameProp[0] = 	$row["real_name1"];
                            $prop[1] = 		$row["property2_fk"];
                            $nameProp[1] = 	$row["real_name2"];

                            $query =	 		$row["query"];
                            $typePropRel =  	null;
                            $realNamePropRel = 	null;

                            if ($relationProperty == null) {
                            //replace null for _ to be used in the index for the array
                                $relationProperty = "_";
                            }
                            $relations[$relationName][$relationProperty][$i] = new Relation();

                            //if it's a property of a relation
                            if ($relationProperty != "_") {
                            //validate if property of relation exists
                                $sql_query = 	"SELECT id, query, type, real_name
												FROM sociql_property 
												WHERE relation_fk = $relationId AND name = '$relationProperty'";
                                $sql_result = DB::query($sql_query, $conn);

                                if ($row = DB::fetchAssoc($sql_result)) {

                                    $query = 			$row["query"];
                                    $realNamePropRel = 	$row["real_name"];
                                    $typePropRel = 		$row["type"];

                                    $relations[$relationName][$relationProperty][$i]->setPropertyRelName($relationProperty);
                                    $relations[$relationName][$relationProperty][$i]->setVariableName($relationVariable);
                                    $relations[$relationName][$relationProperty][$i]->setRealPropertyRelName($realNamePropRel);
                                    $relations[$relationName][$relationProperty][$i]->setVariableType($typePropRel);

                                } else {
                                    return self::raiseValidationError("Relation Property $relationProperty does not exist in the model");
                                }
                            }

                            $relations[$relationName][$relationProperty][$i]->setQuery($query);
                            $relations[$relationName][$relationProperty][$i]->setId($relationId);


                            //check if the variables were defined in From
                            if (isset($objects[$firstObject]) && isset($objects[$secondObject])) {
                                $flagProp[0] = false;
                                $flagProp[1] = false;

                                //iterate over the variables of the relation (which are always 2)
                                for ($j=0; $j<sizeof($prop); $j++) {
                                //validate the type of the actor
                                    $sql_query = 	"SELECT actor_fk, name
													FROM sociql_property 
													WHERE id = " . $prop[$j];

                                    $sql_result = DB::query($sql_query, $conn);

                                    if ($row = DB::fetchAssoc($sql_result)) {

                                        if ($row["actor_fk"] == $objects[$firstObject]->getId() && !$flagProp[0]) {
                                        //name of actors in relation
                                            $relations[$relationName][$relationProperty][$i]->setActorName($firstObject, $j+1);
                                            $relations[$relationName][$relationProperty][$i]->setPropertyName($nameProp[$j], $j+1);
                                            $relations[$relationName][$relationProperty][$i]->setPropertyId($prop[$j], $j+1);

                                            $flagProp[0] = true;

                                        } else if ($row["actor_fk"] == $objects[$secondObject]->getId() && !$flagProp[1]) {
                                            //name of actors in relation
                                                $relations[$relationName][$relationProperty][$i]->setActorName($secondObject, $j+1);
                                                $relations[$relationName][$relationProperty][$i]->setPropertyName($nameProp[$j], $j+1);
                                                $relations[$relationName][$relationProperty][$i]->setPropertyId($prop[$j], $j+1);

                                                $flagProp[1] = true;

                                            } else {
                                                return self::raiseValidationError("Actors in the relation $relationName are not instantiated");
                                            }
                                    }
                                }
                            }

                        } else {
                            return self::raiseValidationError("Relation $relationName does not exist in the model");
                        }

                    } else if ($relationType == 'ONTOLOGY') {

                            $relations[$relationName][$relationProperty][$i] = new Relation('ONTOLOGY');
                            $relations[$relationName][$relationProperty][$i]->setActorName1($firstObject);
                            $relations[$relationName][$relationProperty][$i]->setActorName2($secondObject);

                            $sql_query = 	"SELECT id, level
										FROM sociql_ontology_rel
										WHERE name = '$relationName'";

                            $sql_result = DB::query($sql_query, $conn);

                            if ($row = DB::fetchAssoc($sql_result)) {

                                $relationId = $row["id"];
                                $level = $row["level"];
                                $relations[$relationName][$relationProperty][$i]->setOntologyId($row["id"]);

                                $rels = array($relationId);

                                //iterate until get the lower level entities
                                for (; $level>1; $level--) {

                                    $sql_query = 	"SELECT id
												FROM sociql_ontology_rel
												WHERE ";
                                    for ($j=0; $j<sizeof($rels); $j++) {
                                        $sql_query .= "upper_level = " . $rels[$j] . " OR ";
                                    }
                                    $sql_query = substr($sql_query, 0, strlen($sql_query)-4);

                                    $sql_result = DB::query($sql_query, $conn);

                                    $rels = array();

                                    while ($row = DB::fetchAssoc($sql_result)) {
                                        array_push($rels, $row["id"]);
                                    }
                                }

                                //get the relations from the model
                                $sql_query = 	"SELECT id
											FROM sociql_relation
											WHERE ";
                                for ($j=0; $j<sizeof($rels); $j++) {
                                    $sql_query .= "ont_relation = " . $rels[$j] . " OR ";
                                }
                                $sql_query = substr($sql_query, 0, strlen($sql_query)-4);

                                $sql_result = DB::query($sql_query, $conn);

                                $rels = array();

                                while ($row = DB::fetchAssoc($sql_result)) {
                                    array_push($rels, $row["id"]);
                                }

                                if (sizeof($rels) > 0) {
                                    $relations[$relationName][$relationProperty][$i]->setOntologyRelationIds($rels);
                                } else {
                                    return self::raiseValidationError('Relation ' . $relationName . ' does not have any instance in the model');
                                }
                            } else {
                                return self::raiseValidationError("Relation $relationName does not exist in the model");
                            }
                        }

                } else {
                    //UNDEF
                    $relationName = 	$tree['Relation']['Name'][$i];
                    $firstObject = 		$tree['Relation']['Object1'][$i];
                    $secondObject = 	$tree['Relation']['Object2'][$i];
                    $relationMaxDepth = $tree['Relation']['MaxDepth'][$i];
                    
                    if (isset($objects[$firstObject]) && isset($objects[$secondObject])) {
                        $relations[$relationName]["_"][$i] = new Relation("UNDEF");
                        $relations[$relationName]["_"][$i]->setActorName1($firstObject);
                        $relations[$relationName]["_"][$i]->setActorName2($secondObject);
                        $relations[$relationName]["_"][$i]->setMaxPathLength($relationMaxDepth);
                    } else {
                        return self::raiseValidationError("One or more of the objects in the UNDEF relationship have not been defined");
                    }
                }
            }
        }



        //Conditions in WHERE
        if (isset($tree['Oper'])) {
            for ($i=0; $i<sizeof($tree['Oper']); $i++) {

                $leftType = 		$tree['Left']['Type'][$i];
                $leftPropRelName = 	null;
                $leftObjectName = 	null;
                $leftPropertyName = null;
                $operator = 		$tree['Oper'][$i];
                $value = 			$tree['Right']['Value'][$i];
                $rightType = 		$tree['Right']['Type'][$i];
                $rightObjectName = 	null;
                $rightPropertyName = null;

                if ($leftType != 'RelationVariable') {
                    $leftObjectName = $tree['Left']['Value']['ObjectName'][$i];
                    $leftPropertyName = $tree['Left']['Value']['PropertyName'][$i];
                }

                if (isset($tree['Left']['Value'][$i])) {
                    $leftPropRelName = $tree['Left']['Value'][$i];
                }

                if (isset($tree['Right']['Value']['ObjectName'][$i])) {
                    $rightObjectName = $tree['Right']['Value']['ObjectName'][$i];
                    $rightPropertyName = $tree['Right']['Value']['PropertyName'][$i];
                }

                $objectName = "";
                $propertyName = "";


                if ($leftType == "Variable") {
                    $objectName = $leftObjectName;
                    $propertyName = $leftPropertyName;

                    $conditions[$objectName][$propertyName][$i] = new Condition();
                    $conditions[$objectName][$propertyName][$i]->setOperator($operator);

                    if ($objects[$leftObjectName]->getType() == 'NETWORK') {
                    //validate if left property
                        $sql_query = 	"SELECT id, query, type, sparql
                                                                            FROM sociql_property
                                                                            WHERE name = '$leftPropertyName' AND actor_fk = ".$objects[$leftObjectName]->getId();

                        $sql_result = DB::query($sql_query, $conn);

                        if ($row = DB::fetchAssoc($sql_result)) {

                            $conditions[$objectName][$propertyName][$i]->setLeftId($row["id"]);
                            $conditions[$objectName][$propertyName][$i]->setLeftQuery($row["query"]);
                            $conditions[$objectName][$propertyName][$i]->setSparqlTriplet($row["sparql"]);

                            //if it's nominal, can only have equal or different operators
                            if ($row["type"] == "nominal" && $operator != "=" && $operator != "!=" && $operator != "><" && $operator != "<>" ) {
                                return self::raiseValidationError("The nominal property $leftPropertyName has an inequality operator");
                            }

                            //if it's ordinal, can not have contain operators
                            if ($row["type"] == "ordinal" && ($operator == "><" || $operator == "<>")) {
                                return self::raiseValidationError("The ordinal property $leftpropertyName has a containment operator");
                            }
                        }

                    } else if ($objects[$objectName]->getType() == 'ONTOLOGY') {

                            $propertyId = $this->getOntologyPropertyId($objects[$objectName]->getOntologyId(), $propertyName, $conn);

                            $conditions[$objectName][$propertyName][$i]->setLeftId($propertyId);

                            if ($propertyId === null) {
                                return self::raiseValidationError("Property $propertyName does not exist in the model");
                            }
                        }

                } else if ($leftType == "RelationVariable") {
                        $objectName = $leftPropRelName;
                        $propertyName = "_";

                        $conditions[$objectName][$propertyName][$i] = new Condition();

                        $conditions[$objectName][$propertyName][$i]->setOperator($operator);
                        $conditions[$objectName][$propertyName][$i]->setLeftPropertyRealName($objectName);

                    }



                if ($rightType == 'Variable') {
                //validate if left property
                    $sql_query = 	"SELECT id, query, type, sparql, real_name
                                                                    FROM sociql_property
                                                                    WHERE name = '$rightPropertyName' AND actor_fk = ".$objects[$rightObjectName]->getId();

                    $sql_result = DB::query($sql_query, $conn);

                    if ($row = DB::fetchAssoc($sql_result)) {

                        $conditions[$objectName][$propertyName][$i]->setRightId($row["id"]);
                        $conditions[$objectName][$propertyName][$i]->setRightObjectName($rightObjectName);
                        $conditions[$objectName][$propertyName][$i]->setRightPropertyName($rightPropertyName);
                        $conditions[$objectName][$propertyName][$i]->setRightPropertyRealName($row["real_name"]);
                        $conditions[$objectName][$propertyName][$i]->setRightQuery($row["query"]);

                    }
                } else if ($rightType == 'Constant') {
                        $conditions[$objectName][$propertyName][$i]->setValue($value);
                    }

            }
        }

        if (isset($tree['OrderAlgorithm'])) {
            if (isset($objects[$tree['OrderObject']])) {
            $orderAlgorithm = $tree['OrderAlgorithm'];
            $orderObject = $tree['OrderObject'];
            } else {
                self::raiseError('Order object has not been declared');
            }
        }

        if (isset($tree['Limit'])) {
            $limit = $tree['Limit'];
        }

        self::$query = new Query($objects, $properties, $relations, $conditions, $orderAlgorithm, $orderObject, $limit);
    }


    /**
     * Get ontology id of a property
     * @param int $entityId Id of the ontology entity
     * @param string $propertyName Name of the ontology property
     * @param link_resource $conn Database connection
     * @return int Id of the ontology property
     */
    private function getOntologyPropertyId($entityId, $propertyName, $conn) {

        $propertyId = null;
        $found = false;
        $upperEntity = null;

        //search id of the property
        $sql_query = 	"SELECT upper_entity
                        FROM sociql_ontology_ent
                        WHERE id = $entityId";

        $sql_result = DB::query($sql_query, $conn);

        if ($row = DB::fetchAssoc($sql_result)) {
            $upperEntity = $row['upper_entity'];
        }

        while (!$found) {

        //search id of the property
            $sql_query = 	"SELECT id
                                FROM sociql_ontology_prop
                                WHERE name = '$propertyName' AND (entity_fk = $entityId";
            if ($upperEntity != null) {
                $sql_query .= "OR entity_fk = $upperEntity";
            }
            $sql_query .= ")";

            $sql_result = DB::query($sql_query, $conn);

            if (DB::numRows($sql_result) > 0) {
                if ($row = DB::fetchAssoc($sql_result)) {
                    $propertyId = $row["id"];
                    $found = true;
                }

            } else {
                $sql_query = 	"SELECT to_entity
                                FROM sociql_ontology_rel
                                WHERE from_entity = $entityId AND type = 'I'";
                $sql_result = DB::query($sql_query, $conn);

                if (DB::numRows($sql_result) == 0) {
                    break;
                }

                if ($row = DB::fetchAssoc($sql_result)) {
                    $entityId = $row["to_entity"];
                }

                $sql_query = 	"SELECT upper_entity
                                FROM sociql_ontology_ent
                                WHERE id = $entityId";

                $sql_result = DB::query($sql_query, $conn);

                if ($row = DB::fetchAssoc($sql_result)) {
                    $upperEntity = $row['upper_entity'];
                } else {
                    $upperEntity = null;
                }
            }
        }

        return $propertyId;
    }


    /**
     * Moves the cursor to the next token
     */
    public static function getNextToken() {
        self::$curToken = self::$lexer->getNextToken();
    }


    /**
     * Transforms a number to boolean.  If it is 0, then it returns false.
     * True otherwise
     * @param int $number Number
     * @return boolean If it is 0, then it returns false. True otherwise
     */
    public static function numToBoolean($number) {
        if ($number == 0) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Raise parser error
     * @param string $message Error message
     * @return Exception
     */
    public static function raiseError($message) {
        $message = 'Parse error: '.$message.' on line '. self::$lexer->getCurrentLine();
        $message .= ' and position <i>' . self::$lexer->getCurrentRelativePosition() .'</i>';
        $message .= ' near <i>' . self::$lexer->getCurrentSnippet() . '</i>';

        throw new Exception($message);
    }


    /**
     * Raise validation error
     * @param string Error message
     * @return Exception
     */
    public static function raiseValidationError($message) {
        $message = 'Parse error: '.$message;

        throw new Exception($message);
    }
}
?>