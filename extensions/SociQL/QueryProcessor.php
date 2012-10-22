<?php
/**
 * Process the query, performing oprimizations and actual queries in the data source
 * @package
 * @author Diego Serrano
 * @since 22.05.2010 08:59:00
 */
class QueryProcessor {

    private $selectAttr = array();
    private $subQueries = array();
    private $constConditions = array();
    private $joinConditions = array();
    private $orderAttr = array();

    private $query;


    /**
     * Constructs a new Query Processor object
     */
    function __construct() { }


    /**
     * Get projection attributes of the current query
     * @return array List of projection attributes
     */
    public function getSelectAttributes() {
        return $this->selectAttr;
    }


    /**
     * Finds if an object has the id property among the projected attributes
     * @param string $objectName Name of the object
     * @return boolean Boolean indicating if the object has the id in the projection
     */
    private function selectAttributesHasObjectId($objectName) {
        for ($i=0; $i<sizeof($this->selectAttr); $i++) {
            if ($this->selectAttr[$i]->getObjectName() == $objectName && $this->selectAttr[$i]->isId()) {
                return true;
            }
        }

        return false;
    }


    /**
     * Perform the processing and execution of the query
     * @param Query $query Structured SociQL query
     * @param array $disjunctive List of elements to narrow the results.  Calculated in ExecutionPlanner
     * @param array $selectAttributes List of projection attributes to include in the query
     * @param link_resource $conn Database connection
     * @param int $maxResults Maximum number of results in the query
     * @param boolean $includeOrder Indicates if the query should include an ordering clause
     * @param string $refNumber
     * @return string SQL query
     */
    public function process($query, $disjunctive, $selectAttributes, $conn, $maxResults = 0, $includeOrder = true, $refNumber = "_") {

        $defaultSchema = "";
        if (DB::getDialect() == "DB2") {
            $defaultSchema = "session.";   //DB2
        } else if (DB::getDialect() == "Postgres") {
            $defaultSchema = "";           //Postgress
        }

        $this->query = $query;

        //initialize attributes of select with the parameter
        $this->selectAttr = $selectAttributes;

        //get values from query construct
        $properties = 	$query->getAllProperties();
        $objects = 		$query->getAllObjects();
        $conditions = 	$query->getAllConditions();
        $relations = 	$query->getAllRelations();

        //prepare a query for every property requested in the Select
        foreach ($properties as $objectName=>$propGroup) {
            foreach ($propGroup as $propertyName=>$property) {

                $this->optimizeSubquery($objectName, $propertyName, null, $property->getQuery(), $disjunctive, $conn, $refNumber);
            }
        }

        //try to optimize the queries for condition
        foreach ($conditions as $objectName=>$condGroup) {
            foreach ($condGroup as $propertyName=>$condSubgroup) {
                foreach ($condSubgroup as $condRef=>$condition) {

                //if the condition is not linked to a relation
                    if ($condition->getLeftId() != null && !isset($this->subQueries[$objectName][$propertyName])) {

                        $this->optimizeSubquery($objectName, $propertyName, $condRef, $condition->getLeftQuery(), $disjunctive, $conn, $refNumber);
                    }
                }
            }
        }


        foreach ($conditions as $objectName=>$condGroup) {
            foreach ($condGroup as $propertyName=>$condSubgroup) {
                foreach ($condSubgroup as $condRef=>$condition) {

                //if the condition is to a variable
                    if ($condition->existRightId()) {
                        $leftId = "id";
                        if ($objects[$objectName]->getNameActorId() != "") {
                            $leftId = $objects[$objectName]->getNameActorId();
                        }

                        $rightId = "id";
                        if ($objects[$condition->getRightObjectName()]->getNameActorId() != "") {
                            $rightId = $objects[$condition->getRightObjectName()]->getNameActorId();
                        }

                        $reference = "_";
                        if ($objects[$objectName]->getSiteType() != 'local') {
                            $reference = $refNumber;
                        }

                        array_push($this->constConditions, $objectName . "_" . $propertyName . "_" . $reference . "." . $leftId .
                            " " . $condition->getOperator() . " " . $condition->getRightObjectName() . "_" . $condition->getRightPropertyName() . "_" . key($this->subQueries[$condition->getRightObjectName()][$condition->getRightPropertyName()]) . "." . $rightId);
                    }
                }
            }
        }


        //check if some table is missing in FROM
        foreach ($objects as $objectName=>$object) {
            if (!isset($this->subQueries[$objectName])) {
                $this->subQueries[$objectName]["_"]["_"] = $object->getQuery();

                $nameActorId = 'id';
                if ($object->getNameActorId() != null) {
                    $nameActorId = $object->getNameActorId();
                }

                if (!$object->isUndefObject()) {
                    $reference = "_";
                    if ($object->getSiteType() != 'local') {
                        $reference = $refNumber;
                    }

                    $dataType = "varchar";
                    if (DB::getDialect() == "Postgres") {
                        $dataType = "character varying";
                    } else if (DB::getDialect() == "MySQL") {
                        $dataType = "char";
                    } else if (DB::getDialect() == "DB2") {
                        //$dataType = "varchar";
                    }

                    $projAttribute = new ProjectionAttribute();
                    $projAttribute->setValue("CAST(" . $objectName . "___" . $reference . "." . $object->getNameActorId() . " AS $dataType) AS " . $object->getNameActorId());
                    $projAttribute->setVisibility(false);
                    $projAttribute->setSignificant(false);
                    $projAttribute->setIsId(true);
                    $projAttribute->setObjectName($objectName);
                    $projAttribute->setPropertyName('');
                    $projAttribute->setPreferredName($nameActorId);
                    array_push($this->selectAttr, $projAttribute);
                }

                $numberIds = $objects[$objectName]->getNumberIds() + 1;
                $objects[$objectName]->setNumberIds($numberIds);
            //note: if it came here, it means it does not have conditions to optimize
            }
        }


        //look for the objects that don't have id in projection, and add it
        foreach ($objects as $objectName=>$object) {
            if (!$this->selectAttributesHasObjectId($objectName) && !$object->isUndefObject()) {

                $added = false;

                //check if there is a property for the object
                if (isset($properties[$objectName])) {
                    foreach ($properties[$objectName] as $propertyName=>$property) {
                        $nameActorId = 'id';
                        if ($object->getNameActorId() != null) {
                            $nameActorId = $object->getNameActorId();
                        }

                        $reference = "_";
                        if ($object->getSiteType() != 'local') {
                            $reference = $refNumber;
                        }

                        $dataType = "varchar";
                        if (DB::getDialect() == "Postgres") {
                            $dataType = "character varying";
                        } else if (DB::getDialect() == "MySQL") {
                            $dataType = "char";
                        } else if (DB::getDialect() == "DB2") {
                            //$dataType = "varchar";
                        }

                        $projAttribute = new ProjectionAttribute();
                        $projAttribute->setValue("CAST(" . $objectName . "_" . $propertyName . "_" . $reference . "." . $nameActorId . " AS $dataType) AS " . $nameActorId);
                        $projAttribute->setVisibility(false);
                        $projAttribute->setSignificant(false);
                        $projAttribute->setIsId(true);
                        $projAttribute->setObjectName($objectName);
                        $projAttribute->setPropertyName('');
                        $projAttribute->setPreferredName($nameActorId);
                        array_push($this->selectAttr, $projAttribute);

                        $numberIds = $objects[$objectName]->getNumberIds() + 1;
                        $objects[$objectName]->setNumberIds($numberIds);

                        $added = true;

                        break;
                    }
                }

                if (!$added) {
                //check if there is a condition for the object
                    if (isset($conditions[$objectName])) {
                        foreach ($conditions[$objectName] as $propertyName=>$condSubgroup) {
                            foreach ($condSubgroup as $condRef=>$condition) {

                                $nameActorId = 'id';
                                if ($object->getNameActorId() != null) {
                                    $nameActorId = $object->getNameActorId();
                                }

                                $reference = "_";
                                if ($objects[$objectName]->getSiteType() != 'local') {
                                    $reference = $refNumber;
                                }

                                $dataType = "varchar";
                                if (DB::getDialect() == "Postgres") {
                                    $dataType = "character varying";
                                } else if (DB::getDialect() == "MySQL") {
                                    $dataType = "char";
                                } else if (DB::getDialect() == "DB2") {
                                    //$dataType = "varchar";
                                }

                                $projAttribute = new ProjectionAttribute();
                                $projAttribute->setValue("CAST(" . $objectName . "_" . $propertyName . "_" . $reference . "." . $nameActorId . " AS $dataType) AS " . $nameActorId);
                                $projAttribute->setVisibility(false);
                                $projAttribute->setSignificant(false);
                                $projAttribute->setIsId(true);
                                $projAttribute->setObjectName($objectName);
                                $projAttribute->setPropertyName('');
                                $projAttribute->setPreferredName($nameActorId);
                                array_push($this->selectAttr, $projAttribute);

                                $numberIds = $objects[$objectName]->getNumberIds() + 1;
                                $objects[$objectName]->setNumberIds($numberIds);
                                break;
                            }
                        }
                    }
                }
            }
        }


        //Add join conditions
        foreach ($relations as $relationName=>$relGroup) {

            foreach ($relGroup as $relationPropName=>$relSubgroup) {

                foreach ($relSubgroup as $relationRef=>$relation) {

                //if the relation specifies a query (N-N)
                    if ($relation->getQuery() != "") {

                    //if it has a query specified
                    //substitute ? for the real value
                        if ($relation->existVariableName()) {
                        //note : i did not add support for sparql since the relations dont have properties in rdf

                        //for conditions with constant value
                            if ($conditions[$relation->getVariableName()]["_"][0] != null) {

                            //check if the query contains a WHERE
                                if (strpos(strtoupper($relation->getQuery()), "WHERE ") === FALSE) {
                                    $relation->setQuery($relation->getQuery() . " WHERE ");

                                } else {
                                    $relation->setQuery($relation->getQuery() . " AND ");
                                }

                                //add the property to the query
                                $relation->setQuery($relation->getQuery() . $relation->getRealPropertyRelName() .
                                    $conditions[$relation->getVariableName()]["_"][0]->getOperator() . $conditions[$relation->getVariableName()]["_"][0]->getValue());

                            } else {
                            //NOT WORKING!!!
                            //conditions with variable
								/*$key_split = explode(".", $key);
								$key_split_right = explode(".", trim($whereProperties[$whereRelations[$key]->variableName]->value));
								$name_prop_right = $key_split_right[1];
								
								//get name for property in case it has a special name
								$query = "SELECT real_name FROM sociql_property WHERE actor_fk = ".$fromActors[$key_split_right[0]]->id." AND name = '".$key_split_right[1]."'";
								
								$result = DB::query($query, $conn);
								
								if ($row = DB::fetchAssoc($result))
								{
									if ($row["real_name"] != "")
									{
										$name_prop_right = $row["real_name"];
									}
								}
								
								array_push($this->constConditions, $key_split[0]."_".$key_split[1].".".$whereRelations[$key]->realPropRelName." ".$whereProperties[$whereRelations[$key]->variableName]->operator." ".$key_split_right[0]."_".$key_split_right[1].".".$name_prop_right);
								*/
                            }
                        }


                        $this->subQueries[$relationName][$relationPropName][$relationRef] = $relation->getQuery();

                        //try to optimize with disj. list
                        if (isset($disjunctive[$relationName])) {
                            $this->optimizeSubquery($relationName, $relationPropName, $relationRef, $relation->getQuery(), $disjunctive, $conn, $refNumber);
                        }

                        $nameId1Pk = "";
                        $nameId2Pk = "";
                        $nameId1Fk = $relation->getPropertyName1();
                        $nameId2Fk = $relation->getPropertyName2();
                        if (isset($objects[$relation->getActorName1()])) {
                            $nameId1Pk = $objects[$relation->getActorName1()]->getNameActorId();
                        }
                        if (isset($objects[$relation->getActorName2()])) {
                            $nameId2Pk = $objects[$relation->getActorName2()]->getNameActorId();
                        }

                        //name for primary keys.  If not defined, then it is 'id'
                        if ($nameId1Pk == "") {
                            $nameId1Pk = "id";
                        }
                        if ($nameId2Pk == "") {
                            $nameId2Pk = "id";
                        }

                        //name for primary keys.  If not defined, then it is the same id as the actor
                        if ($nameId1Fk == "") {
                            $nameId1Fk = $objects[$relation->getActorName1()]->getNameActorId();
                        }
                        if ($nameId2Fk == "") {
                            $nameId2Fk = $objects[$relation->getActorName2()]->getNameActorId();
                        }

                        //Add join conditions of the relation
                        if ($relation->getActorName1() != null) {
                            $prop = key($this->subQueries[$relation->getActorName1()]);
                            //$ref = key($this->subQueries[$relation->getActorName1()][$prop]);
                            $ref = "_";
                            if ($objects[$relation->getActorName1()]->getSiteType() != 'local') {
                                $ref = $refNumber;
                            }

                            array_push($this->constConditions, $relation->getActorName1() . "_" . $prop . "_" . $ref . "." .$nameId1Pk .
                                " = " . $relationName . "_" . $relationPropName . "_" . $relationRef . "." . $nameId1Fk);
                        }

                        if ($relation->getActorName2() != null) {
                            $prop = key($this->subQueries[$relation->getActorName2()]);
                            //$ref = key($this->subQueries[$relation->getActorName2()][$prop]);
                            $ref = "_";
                            if ($objects[$relation->getActorName2()]->getSiteType() != 'local') {
                                $ref = $refNumber;
                            }

                            array_push($this->constConditions, $relation->getActorName2() . "_" . $prop . "_" . $ref . "." . $nameId2Pk .
                                " = " . $relationName . "_" . $relationPropName . "_" . $relationRef . "." . $nameId2Fk);
                        }

                    } else if ($relation->getId() != "") {

                        //if the relation does not specify the query (1-1 or 1-N)

                            $props = 	array();
                            $realNames = 	array();

                            $propFlag1 = false;
                            $propFlag2 = false;

                            for ($i=1; $i<=2; $i++) {

                                $prop = $realName = "";

                                $sql_query = 	"SELECT name, query, real_name, actor_fk
											FROM sociql_property 
											WHERE id = ".$relation->getPropertyId($i);

                                $sql_result = DB::query($sql_query, $conn);

                                if ($row = DB::fetchAssoc($sql_result)) {

                                    $prop = $row["name"];

                                    if ($row["real_name"] == "") {
                                        $realName = $row["name"];
                                    } else {
                                        $realName = $row["real_name"];
                                    }

                                    //check if an instance of the table has been created
                                    $tmpObjectName = "";

                                    if ($relation->getPropertyId1() == $relation->getPropertyId($i) && !($propFlag1)) {
                                        $tmpObjectName = $relation->getActorName1();
                                        $props[0] = $prop;
                                        $realNames[0] = $realName;
                                        $propFlag1 = true;

                                    } else if (!($propFlag2)) {
                                            $tmpObjectName = $relation->getActorName2();
                                            $props[1] = $prop;
                                            $realNames[1] = $realName;
                                            $propFlag2 = true;
                                        }

                                    //if it's a new property, then try to optimize
                                    if (!isset($this->subQueries[$tmpObjectName][$prop])) {
                                        $this->optimizeSubquery($tmpObjectName, $prop, null, $row["query"], $disjunctive, $conn, $refNumber);
                                    }

                                }
                            }

                            //
                            $refObj1 = $refObj2 = "_";
                            if ($objects[$relation->getActorName1()]->getSiteType() != 'local') {
                                $refObj1 = $refNumber;
                            }
                            if ($objects[$relation->getActorName2()]->getSiteType() != 'local') {
                                $refObj2 = $refNumber;
                            }

                            array_push($this->constConditions, $relation->getActorName1() . "_" . $props[0] . "_" . $refObj1 .".". $realNames[0] .
                                " = " . $relation->getActorName2() . "_" . $props[1] . "_" . $refObj2 .".". $realNames[1]);
                        }
                }
            }

        }



        //Join conditions between the same actor/relation
        foreach ($this->subQueries as $objRelName=>$subQueriesGroup) {

            $anchorSubQueryPropertyName = key($subQueriesGroup);
            $anchorSubQueryPropertyValue = $subQueriesGroup[$anchorSubQueryPropertyName];
            $anchorSubQueryRef = key($anchorSubQueryPropertyValue);

            if (isset($objects[$objRelName])) {

                $objectName = $objRelName;

                //BETWEEN ACTORS
                //get the name of the id
                $sql_query = 	"SELECT actor_id
                                FROM sociql_actor
                                WHERE id = ".$objects[$objectName]->getId();

                $sql_result = DB::query($sql_query, $conn);

                if ($row = DB::fetchAssoc($sql_result)) {

                    $idName = "id";

                    if ($row["actor_id"] != "") {
                        $idName = $row["actor_id"];
                    }

                    //add the join condition with the other properties of the same actor
                    foreach ($subQueriesGroup as $subQueriesPropName=>$subQueriesSubGroup) {

                        foreach ($subQueriesSubGroup as $subQueryRef=>$subQuery) {
                            if ($subQueriesPropName != $anchorSubQueryPropertyName) {
                                $reference = "_";
                                if ($objects[$objectName]->getSiteType() != 'local') {
                                    $reference = $refNumber;
                                }

                                array_push($this->joinConditions, $objectName . "_" . $anchorSubQueryPropertyName . "_" . $reference . "." . $idName .
                                    " = " . $objectName . "_" . $subQueriesPropName . "_" . $reference . "." . $idName."");
                            }
                        }
                    }
                }

            } else if (isset($relations[$objRelName][$anchorSubQueryPropertyName])) {

                    $relationName = $objRelName;

                    if (isset($relations[$relationName][$anchorSubQueryPropertyName]["_"])) {
                        $type = "relation";
                        $anchorRelation = $relations[$relationName][$anchorSubQueryPropertyName]["_"];
                    } else {
                        $type = "property";
                        $anchorRelation = $relations[$relationName][$anchorSubQueryPropertyName][$anchorSubQueryRef];
                    }

                    //BETWEEN RELATIONS
                    foreach ($subQueriesGroup as $subQueriesPropName=>$subQueriesSubGroup) {
                        foreach ($subQueriesSubGroup as $subQueryRef=>$subQuery) {

                            if ($type == "relation") {
                                $relation = $relations[$relationName][$subQueriesPropName]["_"];
                            } else {
                                $relation = $relations[$relationName][$subQueriesPropName][$subQueryRef];
                            }

                            if (($subQueriesPropName != $anchorSubQueryPropertyName && $subQueryRef != $anchorSubQueryRef) &&
                                (($relation->getActorName1() == $anchorRelation->getActorName1() && $relation->getActorName2() == $anchorRelation->getActorName2()) ||
                                ($relation->getActorName2() == $anchorRelation->getActorName1() && $relation->getActorName1() == $anchorRelation->getActorName2()))) {
                                $name_id1 = "id1";
                                $name_id2 = "id2";

                                if ($relation->getPropertyName1() != "") {
                                    $nameId1 = $relation->getPropertyName1();
                                }

                                if ($relation->getPropertyName2() != "") {
                                    $nameId2 = $relation->getPropertyName2();
                                }

                                array_push($this->joinConditions, $relationName . "_" . $anchorSubQueryPropertyName . "." . $anchorSubQueryRef .
                                    " = " . $relationName . "_" . $subQueriesPropName . "_" . $subQueryRef . "." . $nameId1);
                                array_push($this->joinConditions, $relationName . "_" . $anchorSubQueryPropertyName . "." . $anchorSubQueryRef .
                                    " = " . $relationName . "_" . $subQueriesPropName . "_" . $subQueryRef . "." . $nameId2);
                            }
                        }
                    }
                }
        }



        //get SELECT properties
        foreach ($properties as $objectName=>$propertiesGroup) {

            foreach ($propertiesGroup as $propertyName=>$property) {

                if ($property->isInProjection()) {
                    $realName = $propertyName;

                    //if the name is different, then assign it
                    if ($property->getRealName() != "") {
                        $realName = $property->getRealName();
                    }

                    $isId = false;
                    if ($objects[$objectName]->getNameActorId() == $propertyName) {
                        $isId = true;
                    }

                    $reference = "_";
                    if ($objects[$objectName]->getSiteType() != 'local') {
                        $reference = $refNumber;
                    }
                    $projAttribute = new ProjectionAttribute();
                    $projAttribute->setValue($objectName . "_" . $propertyName . "_" . $reference . "." . $realName);
                    $projAttribute->setVisibility($property->isVisible());
                    $projAttribute->setSignificant($property->isSignificant());
                    $projAttribute->setIsId($isId);
                    $projAttribute->setObjectName($objectName);
                    $projAttribute->setPropertyName($propertyName);
                    if ($property->getPreferredName() != null) {
                        $projAttribute->setPreferredName($property->getPreferredName());
                    } else {
                        $projAttribute->setPreferredName($propertyName);
                    }
                    array_push($this->selectAttr, $projAttribute);

                    //if the property is significant and the actor has not included the id in the result, then include it
                    if ($property->isSignificant() && $objects[$objectName]->getNumberIds() == 0) {
                    //add the id
                        $objectNameId = "id";
                        if ($objects[$objectName]->getNameActorId() != "") {
                            $objectNameId = $objects[$objectName]->getNameActorId();
                        }

                        $dataType = "varchar";
                        if (DB::getDialect() == "Postgres") {
                            $dataType = "character varying";
                        } else if (DB::getDialect() == "MySQL") {
                            $dataType = "char";
                        } else if (DB::getDialect() == "DB2") {
                            //$dataType = "varchar";
                        }

                        $projAttribute = new ProjectionAttribute();
                        $projAttribute->setValue("CAST(" . $objectName . "_" . $reference . "_" . $refNumber . "." . $objectNameId . " AS $dataType) AS " . $objectNameId);
                        $projAttribute->setVisibility(false);
                        $projAttribute->setSignificant(false);
                        $projAttribute->setIsId(true);
                        $projAttribute->setObjectName($objectName);
                        $projAttribute->setPreferredName($propertyName);
                        array_push($this->selectAttr, $projAttribute);

                        $numberIds = $objects[$objectName]->getNumberIds() + 1;
                        $objects[$objectName]->setNumberIds($numberIds);
                    }

                    //add sorting properties
                    if ($property->isSortable()) {
                        array_push($this->orderAttr, $objectName . "_" . $propertyName . "_".$reference."." . $realName);
                    }
                }
            }
        }


        //EXTERNAL SOURCES
        //look in array for remote sources to make temp tables

        // Create our Application instance.
        $facebook = new Facebook(array(
            'appId' => '37271985873',
            'secret' => 'b4d430d0bc25d8029ada6501b72b3503',
            'cookie' => true,
        ));

        //subqueries key val
        foreach ($this->subQueries as $objRelName=>$subQueriesGroup) {

            if (isset($objects[$objRelName])) {
            //external query for an object
                $objectName = $objRelName;

                //get endpoint of site
                $sql_query = 	"SELECT sociql_site.id, endpoint, actor_id, type, username, password, prefixes
                                FROM sociql_site, sociql_actor
                                WHERE sociql_site.id = sociql_actor.site_fk AND
                                        sociql_actor.id = " . $objects[$objectName]->getId() . " AND endpoint != 'local'";
                $sql_result = DB::query($sql_query, $conn);

                if ($row = DB::fetchAssoc($sql_result)) {

                    $siteId = $row["id"];
                    $endpoint = $row["endpoint"];
                    $username = $row["username"];
                    $password = $row["password"];
                    $prefixes = $row["prefixes"];

                    foreach ($subQueriesGroup as $subQueriesPropName=>$subQueriesSubGroup) {

                        foreach ($subQueriesSubGroup as $subQueryRef=>$subQuery) {
                            $results = array();

                            $tableName = $defaultSchema . "" . $objectName . "_" . $subQueriesPropName . "_" . $subQueryRef;
                            $error = false;
                            //verify that the table does no t exist (prevent emptying a table)
                            $exist = $this->existTable($defaultSchema, $tableName, $conn);

                            if (!$exist) {

                                if ($objects[$objectName]->getSiteType() == "yql") {
                                // YQL
                                //for yahoo?  not anymore, use the library
                                //$results = ResultsParser::parse($url, array($actor_id, $key2));
                                //$url = $endpoint . urlencode($val2);

                                } else if ($objects[$objectName]->getSiteType() == "facebook") {
                                    // FACEBOOK

                                        try {
                                            $session = $facebook->getSession();

                                            $me = null;
                                            // Session based API call.
                                            if ($session) {
                                                try {
                                                    $uid = $facebook->getUser();
                                                    $me = $facebook->api('/me');
                                                } catch (FacebookApiException $e) {
                                                    error_log($e);
                                                }
                                            }

                                            if ($me) {
                                            //replace html quotations by '
                                                $subQuery = str_replace("&quot;", "'", $subQuery);

                                                //replace 'me' with user id
                                                $subQuery = str_replace("'me'", $me['id'], $subQuery);
                                                $subQuery = str_replace("'lastWeek'", mktime(0,0,0) - (60*60*24*3), $subQuery);
                                                //echo "<BR><BR>$subQuery";

                                                try {
                                                    $results = $facebook->api(array(
                                                        'method' => 'fql.multiquery',
                                                        'queries' => $subQuery,
                                                    ));

                                                    foreach ($results as $index=>$subQueryFB) {
                                                        if ($subQueryFB['name'] == 'result') {
                                                            $results = $subQueryFB['fql_result_set'];
                                                            break;
                                                        }
                                                    }


                                                } catch(FacebookApiException $e) {
                                                    $msg = 'Exception: 190: Invalid OAuth 2.0 Access Token';
                                                    echo "<error>$msg</error>";
                                                    $error = true;
                                                    //throw new Exception("Facebook error: " . $e->getMessage());
                                                }

                                            } else {
                                                echo "<error>Facebook error: Login to Facebook is needed for this query</error>";
                                                $error = true;
                                                //throw new Exception("Facebook error: Login to Facebook is needed for this query");
                                            }

                                        } catch(Exception $e) {
                                            //?? Accion?
                                            echo '<br/><br/>Message: ' .$e->getMessage() .'<br/><br/>';
                                            $error = true;
                                        }

                                    } else if ($objects[$objectName]->getSiteType() == "sparql") {
                                        // SPARQL

                                        //use single quotation marks --- in db2 insert it as (e.g 'T' === '''T''')
                                        $subQuery = str_replace("&quot;", "'", $subQuery);

                                        /* ARC2 static class inclusion */
                                        include_once('arc/ARC2.php');

                                        /* configuration */
                                        $config = array(
                                            /* remote endpoint */
                                            'remote_store_endpoint' => $endpoint,
                                        );
                                        
                                        /* instantiation */
                                        $store = ARC2::getRemoteStore($config);

                                        echo "<textarea>".htmlspecialchars($prefixes." ".$subQuery)."</textarea>";
                                        if ($results = $store->query($prefixes." ".$subQuery, 'rows')) {
                                            //print_r($results);

                                        } else if (!is_array($results)) {
                                        //???
                                            $error = true;
                                            throw new Exception("Sparql Error: Error in Sparql query ".sizeof($results)."<BR/>".$prefixes." ".htmlspecialchars($subQuery));
                                        }
                                        

                                    } else if ($objects[$objectName]->getSiteType() == "sql") {
                                        /*$host = $endpoint;
                                        $user_db = $username;
                                        $pass_db = $password;
                                        $name_db = 'opensim';

                                        $conn_ext = mysql_connect($host,$user_db,$pass_db)
                                                or die ("No se ha podido conectar con el servidor.");

                                        $db_ext = mysql_select_db($name_db _ext)
                                                or die("No se ha encontrado la base de datos.");

                                        $result_ext_sql = db2_exec($conn, $val2_ext);
                                        while ($row_ext = DB::fetchAssoc($result_ext_sql))
                                        {
                                                array_push($results, $row_ext );
                                        }

                                        $host = 'localhost';
                                        $user_db = 'root';
                                        $pass_db = 'dieguinho';
                                        $name_db = 'reason';

                                        $conn = mysql_connect($host,$user_db,$pass_db)
                                                or die ("No se ha podido conectar con el servidor.");

                                        $db = mysql_select_db($name_db )
                                                or die("No se ha encontrado la base de datos.");*/

                                    } else {
                                        $error = true;
                                        throw new Exception("External Source Error: $objectName is associated to a site not supported");
                                    }

                                if (!$error) {
                                    $tableCreated = false;

                                    if ($objects[$objectName]->getNameActorId() != $subQueriesPropName) {
                                        $tableCreated = $this->createTemporaryTable($tableName, $objects[$objectName]->getNameActorId(), $subQueriesPropName, $conn);
                                    } else {
                                    //When the property is the same as the id of the object
                                        $tableCreated = $this->createTemporaryTable($tableName, $objects[$objectName]->getNameActorId(), null, $conn);
                                    }

                                    if ($tableCreated) {

                                        foreach ($results as $tupleKey=>$tupleValue) {

                                            $countCol = 0;
                                            $insertValues = array();
                                            $valuesOrder = 'IdFirst';

                                            foreach ($tupleValue as $dataType=>$dataValue) {

                                            //if it is sparql and ends with '_type' dont count
                                                if ($objects[$objectName]->getSiteType() == "sparql" && substr($dataType, strlen($dataType) - strlen(" type")) == " type") {
                                                    continue;
                                                }

                                                $countCol ++;

                                                $insertValue = $dataValue;
                                                while (is_array($insertValue)) {
                                                //gets the innermost data
                                                    $insertValue = array_pop($insertValue);
                                                }

                                                //array_push($insertValues, QueryProcessor::cleanString(str_replace("'", "''", $insert_val)));
                                                array_push($insertValues, QueryProcessor::cleanString($insertValue));

                                                if ($objects[$objectName]->getSiteType() == "facebook" &&
                                                    $objects[$objectName]->getNameActorId() != $dataType && $countCol == 1) {
                                                    $valuesOrder = 'ValueFirst';
                                                }
                                            }

                                            if ($valuesOrder == 'ValueFirst') {
                                                $tmp = $insertValues[0];
                                                $insertValues[0] = $insertValues[1];
                                                $insertValues[1] = $tmp;
                                            }

                                            $recordInserted = "";
                                            if ($objects[$objectName]->getNameActorId() != $subQueriesPropName) {
                                                $recordInserted = $this->insertIntoTemporaryTable($tableName, $objects[$objectName]->getNameActorId(), $subQueriesPropName,
                                                    $insertValues[0], $insertValues[1], $conn);
                                            } else {
                                                $recordInserted = $this->insertIntoTemporaryTable($tableName, $objects[$objectName]->getNameActorId(), null,
                                                    $insertValues[0], null, $conn);
                                            }


                                            if (!$recordInserted) {
                                                echo "<b>External source error: Problem inserting some records in temporary table ".$insertValues[1]."</b><br/>";
                                            }
                                        }
                                    }
                                    else {
                                        throw new Exception("External source error: Problem creating temporary tables ".$tableName);
                                    }
                                }
                            }

                            unset($this->subQueries[$objectName][$subQueriesPropName][$subQueryRef]);

                            if (!$error) {
                            //correct the subqueries to correspond to the temporary table
                                if ($objects[$objectName]->getNameActorId() != $subQueriesPropName) {
                                    $this->subQueries[$objectName][$subQueriesPropName][$refNumber] = "SELECT " . $objects[$objectName]->getNameActorId() . ", $subQueriesPropName FROM $tableName";
                                } else {
                                    $this->subQueries[$objectName][$subQueriesPropName][$refNumber] = "SELECT " . $objects[$objectName]->getNameActorId() . " FROM $tableName";
                                }
                            }
                        }
                    }
                }

            } else if (isset($relations[$objRelName])) {

                //RELATIONS
                    $relationName = $objRelName;

                    foreach ($subQueriesGroup as $subQueriesPropName=>$subQueriesSubGroup) {

                        foreach ($subQueriesSubGroup as $subQueryRef=>$subQuery) {

                        //get endpoint of site
                            $siteId = 	array();
                            $endpoint = array();
                            $objectId = array();
                            $siteType = array();
                            $siteType[0] = 'local';
                            $siteType[1] = 'local';

                            if (isset($objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName1()])) {
                                $siteId[0] =	$objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName1()]->getSiteId();
                                $siteType[0] = 	$objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName1()]->getSiteType();
                                $objectId[0] = 	$objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName1()]->getNameActorId();
                                if ($objectId[0] == "") {
                                    $objectId[0] = 	$relations[$relationName][$subQueriesPropName][$subQueryRef]->getPropertyName1();
                                }
                            }

                            if (isset($objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName2()])) {
                                $siteId[1] = 	$objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName2()]->getSiteId();
                                $siteType[1] = 	$objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName2()]->getSiteType();
                                $objectId[1] = 	$objects[$relations[$relationName][$subQueriesPropName][$subQueryRef]->getActorName2()]->getNameActorId();
                                if ($objectId[1] == "") {
                                    $objectId[1] = 	$relations[$relationName][$subQueriesPropName][$subQueryRef]->getPropertyName2();
                                }
                            }

                            //note: if it is not from the same site, then the relation is 1-1 or 1-N
                            if ($siteType[0] != "local" && $siteType[1] != "local") {

                                if ($siteType[0] == "") {
                                //only for planner
                                    $objectKey = key($objects);
                                    $objectType = $objects[$objectKey]->getSiteType();

                                    $siteType[0] = $objectType;
                                    $siteType[1] = $objectType;

                                }

                                $tableName = $relationName . "_" . $subQueriesPropName . "_" . $subQueryRef;

                                $exist = $this->existTable($defaultSchema, $tableName, $conn);

                                if (!$exist) {

                                    if ($siteType[0] == "yql") {
                                    // YQL
                                    //$url = $endpoint . urlencode($val2);
                                    //$results = ResultsParser::parse($url, array($actor_id, $key2));

                                    } else if ($siteType[0] == "facebook") {
                                        // FACEBOOK

                                            try {
                                                $session = $facebook->getSession();

                                                $me = null;
                                                // Session based API call.
                                                if ($session) {
                                                    try {
                                                        $uid = $facebook->getUser();
                                                        $me = $facebook->api('/me');
                                                    } catch (FacebookApiException $e) {
                                                        error_log($e);
                                                    }
                                                }

                                                if ($me) {
                                                //use single quotation marks --- in db2 insert it as (e.g 'T' === '''T''')
                                                    $subQuery = str_replace("&quot;", "'", $subQuery);

                                                    $subQuery = str_replace("'me'", $me['id'], $subQuery);
                                                    //echo "<BR><BR>$subQuery";

                                                    try {
                                                        $results = $facebook->api(array(
                                                            'method' => 'fql.multiquery',
                                                            'queries' => $subQuery,
                                                        ));

                                                        foreach ($results as $index=>$subQueryFB) {
                                                            if ($subQueryFB['name'] == 'result') {
                                                                $results = $subQueryFB['fql_result_set'];
                                                                break;
                                                            }
                                                        }
                                                    //$results = $results['result'];

                                                    } catch(FacebookApiException $e) {
                                                        $msg = 'Exception: 190: Invalid OAuth 2.0 Access Token';
                                                    }
                                                } else {
                                                    throw new Exception("Facebook Error: Login to Facebook is needed for this query");
                                                }
                                            }
                                            catch(Exception $e) {
                                                throw new Exception("Facebook error: problem accessing Facebook. ".$e->getMessage());
                                            }
                                        }


                                    $tableCreated = false;
                                    if ($objectId[0] != "" && $objectId[1] != "") {
                                        $tableCreated = $this->createTemporaryTable($tableName, $objectId[0], $objectId[1], $conn);

                                    } else if ($objectId[0] != "") {
                                            $tableCreated = $this->createTemporaryTable($tableName, $objectId[0], null, $conn);

                                        } else if ($objectId[1] != "") {
                                                $tableCreated = $this->createTemporaryTable($tableName, $objectId[1], null, $conn);
                                            }

                                    if ($tableCreated) {

                                        foreach ($results as $tupleKey=>$tupleValue) {

                                            $countCol = 0;
                                            $insertValues = array();

                                            foreach ($tupleValue as $dataType=>$dataValue) {

                                                $countCol ++;
                                                $dataValue = str_replace("\'", "''", $dataValue);

                                                array_push($insertValues, $dataValue);
                                            }

                                            $recordInserted = false;
                                            if ($objectId[0] != "" && $objectId[1] != "") {
                                                $recordInserted = $this->insertIntoTemporaryTable($tableName, $objectId[0], $objectId[1],
                                                    $insertValues[0], $insertValues[1], $conn);

                                            } else if ($objectId[0] != "") {
                                                    $recordInserted = $this->insertIntoTemporaryTable($tableName, $objectId[0], null,
                                                        $insertValues[0], null, $conn);

                                                } else if ($objectId[1] != "") {
                                                        $recordInserted = $this->insertIntoTemporaryTable($tableName, $objectId[1], null,
                                                            $insertValues[1], null, $conn);
                                                    }


                                            if ($recordInserted) {
                                            //echo "<br>  ----------------V RECORD INSERTED";
                                            } else {
                                                echo "<b>ERROR: problem inserting record</b>";
                                            }
                                        }
                                    }
                                    else {
                                        throw new Exception("ERROR: problem creating tables ".$create_table);
                                    }
                                }


                                //correct the subqueries
                                $this->subQueries[$relationName][$subQueriesPropName][$subQueryRef] = "SELECT " . $objectId[0] . ", " . $objectId[1] . " FROM $tableName";
                            }
                        }
                    }
                }
        }


        return $this->createSQLQuery($maxResults, $includeOrder);
    }


    /**
     * Creates a SQL query from the data contained in the object after processing
     * @param int $maxResults Maximum number of results in the query
     * @param boolean $includeOrder Indicates if the query should include an ordering clause
     * @return string SQL query
     */
    private function createSQLQuery($maxResults, $includeOrder) {
    //prepare query
        $sqlQuery = "SELECT ";
        for ($i=0; $i<sizeof($this->selectAttr); $i++) {
            $sqlQuery .= $this->selectAttr[$i]->getValue();

            if ($i<sizeof($this->selectAttr)-1) {
                $sqlQuery .= ", ";
            }
        }

        $sqlQuery .= " FROM ";

        foreach ($this->subQueries as $subQueriesObjRelName=>$subQueriesGroup) {
            foreach ($subQueriesGroup as $subQueriesPropName=>$subQueriesSubGroup) {
                foreach ($subQueriesSubGroup as $subQueryRef=>$subQuery) {

                    $sqlQuery .= "($subQuery) AS " . $subQueriesObjRelName . "_" . $subQueriesPropName . "_" . $subQueryRef . ", ";
                }
            }
        }

        //get rid off last ,
        $sqlQuery = substr($sqlQuery, 0, strlen($sqlQuery)-2);

        if (sizeof($this->joinConditions) > 0 || sizeof($this->constConditions) > 0) {

        //joins
            $sqlQuery .= " WHERE ".  implode(" AND ", $this->joinConditions);

            if (sizeof($this->joinConditions) > 0 && sizeof($this->constConditions) > 0) {
                $sqlQuery .= " AND ";
            }

            //constants
            $sqlQuery .= "  ".  implode(" AND ", $this->constConditions);
        }

        if ($includeOrder && sizeof($this->orderAttr) > 0) {
            $sqlQuery .= " ORDER BY ". implode(", ", $this->orderAttr);
        }

        if ($maxResults > 0) {

            if (DB::getDialect() == "DB2") {
                $sqlQuery .= " FETCH FIRST $max_results ROWS ONLY"; //DB2

            } else if (DB::getDialect() == "MySQL") {
                    $sqlQuery .= " LIMIT 0, ".$max_results;				//MySQL

                } else if (DB::getDialect() == "Postgres") {
                        $sqlQuery .= " LIMIT " . $maxResults;				//Postgres
                    }
        }

        return $sqlQuery;
    }


    /**
     * Optimizes subqueries adding conditions
     * @param string $objectName Object name
     * @param string $propertyName Property name
     * @param string $reference Reference
     * @param string $sqlQuery SQL query
     * @param array $disjunctive Disjunctive list from Execution planner
     * @param link_resource $conn Database connection
     * @param int $refNumber Reference number
     */
    public function optimizeSubquery($objectName, $propertyName, $reference, $sqlQuery, $disjunctive, $conn, $refNumber = null) {

        $lowercaseFunction = "LOWER";
        if (DB::getDialect() == "DB2") {
            $lowercaseFunction = "LCASE";    //DB2
        } else if (DB::getDialect() == "Postgres") {
            $lowercaseFunction = "LOWER";      //Postgress
        } else if (DB::getDialect() == "MySQL") {
            $lowercaseFunction = "";      //MySQL
        }

        $properties = 	$this->query->getAllProperties();
        $objects = 	$this->query->getAllObjects();
        $conditions = 	$this->query->getAllConditions();
        $relations = 	$this->query->getAllRelations();

        //check if it's optimizable
        $optimizable = true;
        $sql_query = "SELECT optimizable
                    FROM sociql_property
                    WHERE actor_fk = ".$objects[$objectName]->getId()." AND name = '".$propertyName."'";

        $sql_result = DB::query($sql_query, $conn);

        if ($row = DB::fetchAssoc($sql_result)) {
            if ($row["optimizable"] == 0) {
                $optimizable = false;
            }
        }

        if ($reference === null) {
            $reference = "_";
        }

        //if it's from facebook and has a disjunctive list, then change the base query
        if (isset($objects[$objectName]) && $objects[$objectName]->getSiteType() == "facebook" && isset($disjunctive[$objectName])) {

            //set the query to be used
            $sql_query = "SELECT fb_disj_query
                        FROM sociql_property
                        WHERE actor_fk = ".$objects[$objectName]->getId()." AND name = '".$propertyName."'";
            $sql_result = DB::query($sql_query, $conn);

            if ($row = DB::fetchAssoc($sql_result)) {
                if ($row["fb_disj_query"] != "") {
                    $sqlQuery = $row["fb_disj_query"];
                }
            }
        }


        //Add conditions
        $tempConditions = array();
        $tempConditionsSparql = array();

        if (isset($conditions[$objectName]) && $optimizable) {

            foreach ($conditions[$objectName] as $condPropertyName=>$condSubgroup) {

                foreach ($condSubgroup as $condRef=>$condition) {

                //only tries to optimize if the value of the condition is constant, and it's not a variable for relprop
                    if (!($condition->existRightId()) && $condition->getLeftId() != null) {

                        $tempPropertyName = $condPropertyName;

                        //set real name to be used in property
                        $sql_query = 	"SELECT id, real_name, sparql
										FROM sociql_property 
										WHERE actor_fk = " . $objects[$objectName]->getId()." AND name = '" . $condPropertyName . "'";

                        $sql_result = DB::query($sql_query, $conn);

                        if ($row = DB::fetchAssoc($sql_result)) {

                            if ($row["real_name"] != "") {
                                $tempPropertyName = $row["real_name"];
                            }

                            $sparqlTriplet = $row["sparql"];
                        }

                        //prepend str(?...) to properties for dbpedia (sparql)
                        if ($objects[$objectName]->getSiteType() == "sparql") {
                            $tempPropertyName = "str(?" . $tempPropertyName . ")";
                        }

                        //add sparql triplet
                        array_push($tempConditionsSparql, $sparqlTriplet);

                        //if it's SQL, then add the name of the actor.property
                        if ($objects[$objectName]->getSiteType() == "local" || $objects[$objectName]->getSiteType() == "sql") {
                            $tempPropertyName = $objects[$objectName]->getRealName() . "." . $tempPropertyName;
                        }

                        if ($condition->getOperator() != "><" && $condition->getOperator() != "<>") {
                            array_push($tempConditions, $tempPropertyName . " " . $condition->getOperator() . " " . $condition->getValue());

                        } else if ($objects[$objectName]->getSiteType() == "local" || $objects[$objectName]->getSiteType() == "sql") {
                                
                                $condValue = substr($condition->getValue(), 1, strlen($condition->getValue())-2);
                                if (DB::getDialect() != "MySQL") {
                                    $condValue = strtolower($condValue);
                                }
                                
                                if ($condition->getOperator() == "><") {
                                //contains
                                    array_push($tempConditions, $lowercaseFunction . "(" . $tempPropertyName . ") LIKE '%$condValue%'");
                                } else {
                                //not contains
                                    array_push($tempConditions, $lowercaseFunction . "(" . $tempPropertyName . ") NOT LIKE '%$condValue%'");
                                }

                        } else if ($objects[$objectName]->getSiteType() == "sparql") {

                                if ($condition->getOperator() == "><") {
                                //contains
                                    array_push($tempConditions, "regex(" . $tempPropertyName . ", " . $condition->getValue() . ")");
                                } else {
                                //not contains
                                    array_push($tempConditions, "!(regex(" . $tempPropertyName . ", " . $condition->getValue() . "))");
                                }

                        } else {
                            if ($propertyName == $condPropertyName) {
                                $condValue = substr($condition->getValue(), 1, strlen($condition->getValue())-2);
                                if (DB::getDialect() != "MySQL") {
                                    $condValue = strtolower($condValue);
                                }

                                if ($condition->getOperator() == "><") {
                                //contains
                                //if LIKE is not supported, then add to the constConditions
                                    array_push($this->constConditions, $lowercaseFunction . "(" . $objectName . "_" . $condPropertyName . "__." . $tempPropertyName .
                                        ") LIKE '%$condValue%'");
                                } else {
                                //not contains
                                    array_push($this->constConditions, $lowercaseFunction . "(" . $objectName . "_" . $condPropertyName . "__." . $tempPropertyName .
                                        ") NOT LIKE '%$condValue%'");
                                }
                            }
                        }
                    }
                }
            }
        }

        $reference = "_";
        if ($refNumber != null && $objects[$objectName]->getSiteType() != 'local') {
            $reference = $refNumber;
        }

        $this->subQueries[$objectName][$propertyName][$reference] = $sqlQuery;

        //add conditions to optimize
        if (sizeof($tempConditions) > 0) {

            $glueWord = " WHERE ";

            if ($objects[$objectName]->getSiteType() != "sparql" && (strpos($this->subQueries[$objectName][$propertyName][$reference], " WHERE ") != FALSE)) {
                $glueWord = " AND ";

            } else if ($objects[$objectName]->getSiteType() == "sparql") {
                    $glueWord = " FILTER ( ";
                }

            if ($objects[$objectName]->getSiteType() == "sparql") {

                $additional = " ";

                for ($i=0; $i<sizeof($tempConditions); $i++) {

                    if (strpos($sqlQuery, $tempConditionsSparql[$i]) === FALSE) {
                        $additional .= $tempConditionsSparql[$i] . " . ";
                    }

                    $additional .= " $glueWord ". $tempConditions[$i] . "). ";
                }

                $posRightBracket = strripos($this->subQueries[$objectName][$propertyName][$reference], "}");

                if ($posRightBracket !== FALSE) {
                    $this->subQueries[$objectName][$propertyName][$reference] = substr($this->subQueries[$objectName][$propertyName][$reference], 0, $posRightBracket) . $additional . " }";
                }

            } else {
                $additional = " $glueWord ". implode(" AND ", $tempConditions);

                if ($objects[$objectName]->getSiteType() == "facebook") {
                    $this->subQueries[$objectName][$propertyName][$reference] = str_replace('"}', $additional.'"}', $this->subQueries[$objectName][$propertyName][$reference]);
                } else {
                    $this->subQueries[$objectName][$propertyName][$reference] .= $additional;
                }
            }
        }



        //Add disjunctive objects
        if (isset($disjunctive[$objectName])) {

            $additional = array();
            $sparqlTriplets = array();

            foreach ($disjunctive[$objectName] as $disjPropertyId=>$disjunction) {

                $realName = "";

                if (isset($objects[$objectName])) {

                    $sql_query = 	"SELECT id, name, real_name, sparql
									FROM sociql_property 
									WHERE actor_fk = " . $objects[$objectName]->getId() . " AND id = " . $disjPropertyId ;
                    $sql_result = DB::query($sql_query, $conn);

                    if ($row = DB::fetchAssoc($sql_result)) {

                        if ($row["real_name"] != "") {
                            $realName = $row["real_name"];
                        } else {
                            $realName = $row["name"];
                        }

                        //add triplet if necessary
                        if ($row["sparql"] != "" && strpos($this->subQueries[$objectName][$propertyName], $row["sparql"]) === FALSE) {
                            array_push($sparqlTriplets, $row["sparql"]);
                        }
                    }

                } /*else if (isset($relations[$objectName]) &&
						isset($objects[$relations[$objectName]->getActorName1()]->getId()) && 
						isset($objects[$relations[$objectName]->getActorName2()]->getId())) {
					//$rel_name = explode("_", $key_split[0]);
					//$rel_name = $rel_name[0];
					$sql_query = 	"SELECT id, property1_fk, property2_fk, real_name1, real_name2 
									FROM sociql_relation 
									WHERE id = ". $relations[$objectName]->getId();
			
					$sql_result = DB::query($sql_query, $conn);
					
					if ($row = DB::fetchAssoc($sql_result)) {
						
						if ($row["property1_fk"] == $disjPropertyName) {
							
							if ($row["real_name1"] != "") {
								$realName = $row["real_name1"];
							} else {
								$sql_query = 	"SELECT name, real_name 
												FROM sociql_property 
												WHERE id = " . $row["property1_fk"];
								$sql_result = DB::query($sql_query, $conn);
								
								if ($row = DB::fetchAssoc($sql_result)) {
									
									if ($row["real_name"] != "") {
										$realName = $row["real_name"];
									} else {
										$realName = $row["name"];
									}
								}
							}
						} else if ($row["property2_fk"] == $disjPropertyName) {
							if ($row["real_name2"] != "") {
								$realName = $row["real_name2"];
							} else {
								$sql_query = 	"SELECT name, real_name 
												FROM sociql_property 
												WHERE id = " . $row["property2_fk"];
								$sql_result = DB::query($sql_query, $conn);
								
								if ($row = DB::fetchAssoc($sql_result)) {
									if ($row["real_name"] != "") {
										$realName = $row["real_name"];
									} else {
										$realName = $row["name"];
									}
								}
							}
						}
					}
				} else if (isset($relations[$objectName]) && ($relations[$objectName]->getActorName1() == "" || $relations[$objectName]->getActorName2() == "")) {
					
					if ($relations[$objectName]->getActorName1() == "") {
						$realName = $relations[$objectName]->getPropertyName2();
					} else {
						$realName = $relations[$objectName]->getPropertyName1();
					}
				}
				*/
                if ($realName != "") {

                    for ($i=0; $i<sizeof($disjunction) && $i<50; $i++) {

                        if (trim($disjunction[$i]) != "") {

                            if ($objects[$objectName]->getSiteType() == "sparql" && strpos($disjunction[$i], "http://") === FALSE) {
                                $item = "str(?". $realName .") = '".$disjunction[$i]."'";
                            } else if ($objects[$objectName]->getSiteType() == "sparql") {
                                    $item = "?".$realName." = <".$disjunction[$i].">";
                                } else {
                                    $item = $realName." = '".$disjunction[$i]."'";
                                }
                            array_push($additional, $item);
                        }
                    }
                }
            }

            if (sizeof($additional) > 0) {
            //echo "<br>Cual tipo es? ";
                if ($objects[$objectName]->getSiteType() == "sparql") {

                    foreach ($this->subQueries[$objectName][$propertyName] as $subQueryRef=>$subQuery) {
                        $posRightBracket = strripos($this->subQueries[$objectName][$propertyName][$subQueryRef], "}");

                        if ($posRightBracket !== FALSE) {
                            $this->subQueries[$objectName][$propertyName][$subQueryRef] = substr($this->subQueries[$objectName][$propertyName][$subQueryRef], 0, $posRightBracket) . " ". implode(" . ", $sparqlTriplets)." FILTER ( ".implode(" || ", $additional) . "). " . " }";
                        }
                    }

                } else {

                    foreach ($this->subQueries[$objectName][$propertyName] as $subQueryRef=>$subQuery) {

                        if (strpos($this->subQueries[$objectName][$propertyName][$subQueryRef], "WHERE") === FALSE) {
                            if ($objects[$objectName]->getSiteType() == "facebook") {
                                $this->subQueries[$objectName][$propertyName][$subQueryRef] = str_replace('"}', ' WHERE "}', $this->subQueries[$objectName][$propertyName][$reference]);
                            } else {
                                $this->subQueries[$objectName][$propertyName][$subQueryRef] .= " WHERE ";
                            }
                        } else {
                            if ($objects[$objectName]->getSiteType() == "facebook") {
                                $this->subQueries[$objectName][$propertyName][$subQueryRef] = str_replace('"}', ' AND "}', $this->subQueries[$objectName][$propertyName][$reference]);
                            } else {
                                $this->subQueries[$objectName][$propertyName][$subQueryRef] .= " AND ";
                            }
                        }

                        if ($objects[$objectName]->getSiteType() == "facebook") {
                            $this->subQueries[$objectName][$propertyName][$subQueryRef] = str_replace('"}', "(".implode(" OR ", $additional).')"}', $this->subQueries[$objectName][$propertyName][$reference]);
                        } else {
                            $this->subQueries[$objectName][$propertyName][$subQueryRef] .= "(".implode(" OR ", $additional).")";
                        }
                    }
                }
            }
        }

    }


    /**
     *
     * @param <type> $schemaName
     * @param <type> $tableName
     * @param <type> $conn
     * @return <type>
     */
    private function existTable($schemaName, $tableName, $conn) {
        if (DB::getDialect() == "Postgres") {
            if ($schemaName == "") {
                $schemaName = "pg_temp_";
            }

            $sql_query = "select * from pg_tables where substring(schemaname from 0 for ".(strlen($schemaName)+1).") LIKE '$schemaName' AND tablename='$tableName'";
            $sql_result = DB::query($sql_query, $conn);

            if (DB::numRows($sql_result) > 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * Create a temporary table in the host database schema
     * @param string $tableName Table name
     * @param string $field1 Table field 1 (id)
     * @param string $field2 Table field 2
     * @param link_resource $conn Database connection
     * @return boolean Boolean indicating if the operation was successful
     */
    private function createTemporaryTable($tableName, $field1, $field2, $conn) {

        if (DB::getDialect() == "DB2") {
            //Temporary table DB2
            $createStmt = "DECLARE GLOBAL TEMPORARY TABLE $tableName ( " .
                "$field1 VARCHAR(255)";

            if ($field2 != null) {
                $createStmt .= ", $field2 VARCHAR(255)";
            }

            $createStmt .= ") NOT LOGGED ON COMMIT PRESERVE ROWS";

        } else if (DB::getDialect() == "Postgres") {
            //Temporary table in Postgres
            $createStmt = "CREATE TEMPORARY TABLE $tableName ( ".
                "$field1 VARCHAR(255)";

            if ($field2 != null) {
                $createStmt .= ", $field2 VARCHAR(255)";
            }
            $createStmt .= ")";
        }

        //echo "<BR>$createStmt<BR>";
        if (DB::query($createStmt, $conn)) {
            return true;
        }

        return false;
    }

    
    /**
     * Inserts a tuple in a temporary table
     * @param string $tableName Table name
     * @param string $field1 Table field 1 (id)
     * @param string $field2 Table field 2
     * @param string $value1 Value for field 1
     * @param string $value2 Value for field 1
     * @param link_resource $conn Database connection
     * @return boolean Boolean indicating if the operation was successful
     */
    private function insertIntoTemporaryTable($tableName, $field1, $field2, $value1, $value2, $conn) {
        $insertStmt = "INSERT INTO $tableName ( $field1";

        if ($field2 != null) {
            $insertStmt .= ", $field2 ";
        }

        $insertStmt .= ") VALUES ( '$value1'";

        if ($field2 != null) {
            $insertStmt .= ", '" . QueryProcessor::cleanString(substr($value2, 0, 255)) . "'";
        }

        $insertStmt .= ")";
        //echo "<BR>$insertStmt<BR>";
        if (DB::query($insertStmt, $conn)) {
            return true;
        }
        //echo "<BR>$insertStmt<BR>";
        return false;
    }


    /**
     * Clean a string from not supported characters
     * @param string $s String
     * @return string Clean string
     */
    public static function cleanString($s) {
        /*$s = ereg_replace("[����]","a",$s);
        $s = ereg_replace("[����]","A",$s);
        $s = ereg_replace("[���]","I",$s);
        $s = ereg_replace("[���]","i",$s);
        $s = ereg_replace("[���]","e",$s);
        $s = ereg_replace("[���]","E",$s);
        $s = ereg_replace("[�����]","o",$s);
        $s = ereg_replace("[����]","O",$s);
        $s = ereg_replace("[���]","u",$s);
        $s = ereg_replace("[���]","U",$s);
        $s = str_replace("�","c",$s);
        $s = str_replace("�","C",$s);
        $s = str_replace("[�]","n",$s);
        $s = str_replace("[�]","N",$s);*/

        $s = str_replace("'", "''", $s);
        $s = ereg_replace("[^A-Za-z0-9/:,;=\_. -\']", "", $s);


        return $s;
    }
}
?>