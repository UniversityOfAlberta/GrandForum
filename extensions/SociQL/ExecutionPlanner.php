<?php
/**
 *
 * Controls the execution of every part of the query.  as some of the queries are 
 * stated at a higher level, then this an ExecutionPlanner translates the query to
 * elements at the lowest level.
 * @package ?
 * @author Diego Serrano
 * @since 23.05.2010 04:55:00
 */

include_once "Object.php";
include_once "Relation.php";
include_once "Property.php";
include_once "Condition.php";
include_once "QueryProcessor.php";
include_once "Path.php";
include_once "ProjectionAttribute.php";
		

class ExecutionPlanner {
	
	private $queries = array();
	private $sqlQueries = array();
	private $selectAttributes = array();
	
	private $disjunctive = array();
	private $queueDisjunctive = array();

	//initialize arrays resulting from applying UNDEF relations
	private $undefPathNames = array();
	private $undefPathValues = array();
	private $undefPathObjects = array();
	
	/**
     * Constructs an ExecutionPlanner object
     */
	function __construct() { }
	
	/**
     * Get the attributes to be projected in the result of the query
     * @return array Array of ProjectionAttribute objects.
     */
	public function getProjectionAttributes() {
		return $this->selectAttributes;
	} 
	
	/**
     * Process and executes a SociQL query
     * @param string $query SociQL query
     * @param int $maxResults Maximum number of tuples in the result set.  Infinite by default.
     * @return string SQL query in the proper dialect
     */
	public function execute($query, $maxResults = 0) {
		
		include "db.inc.php";
		
		//get values from query construct
		$properties =	$query->getAllProperties();
		$objects = 		$query->getAllObjects();
		$conditions = 	$query->getAllConditions();
		$relations = 	$query->getAllRelations();
		
		
		$ontQueries = $this->getOntologyQueries($query, $conn);
		
		$unprocessedQueries = array();
		if (sizeof($ontQueries) > 0) {
			$unprocessedQueries = $ontQueries; 
		} else {
			$unprocessedQueries = array($query);
		}
		
		
		$isUndef = false;
		$selectAttributesGlobal = array();
		
		for ($i=0; $i<sizeof($unprocessedQueries); $i++) {
			
			//create array for paths of undefined relations
			$undefPaths = array();
			$undefNames = array();
			
			$properties =	$unprocessedQueries[$i]->getAllProperties();
			$objects = 	$unprocessedQueries[$i]->getAllObjects();
			$conditions = 	$unprocessedQueries[$i]->getAllConditions();
			$relations = 	$unprocessedQueries[$i]->getAllRelations();
			
			//get undefined relationships
			foreach ($relations as $relationName=>$relGroup) {
				
				foreach ($relGroup as $relationPropName=>$relSubgroup) {
					
					foreach ($relSubgroup as $relationRef=>$relation) {
			
						if ($relationName == "UNDEF") {
							
							$isUndef = true;
							
							$undefPaths[$relationRef] = $this->findPaths($objects[$relation->getActorName1()]->getId(), $objects[$relation->getActorName2()]->getId(), 
																		$relation->getMaxPathLength(), $conn);
							
							array_push($undefNames, $relationRef);
							
							if (sizeof($undefPaths[$relationRef]) == 0) {
								throw new Exception("ERROR: No paths found for undefined relation");
							}
						}
					}
				}
			}
			
			if ($isUndef) {
				//process UNDEF relations
				$this->createQueriesFromUndef($unprocessedQueries[$i], $undefPaths, $undefNames, $conn);
			} else {
				array_push($this->queries, $unprocessedQueries[$i]);
			}
		}
		
		
		
		if (!$isUndef && sizeof($unprocessedQueries) == 0) {
			//if there are no UNDEF relations and no ontological elements
			array_push($this->queries, $query);
		}
		
		
		//result queries
		$resultQueries = array();
		
		for ($i=0; $i<sizeof($this->queries); $i++) {
			
			//add path label to result in query
			$selectAttributesGlobal = array();
            
			if (isset($this->undefPathValues[$i])) {
				for ($j=0; $j<sizeof($this->undefPathValues[$i]); $j++) {
					
					//attribute for sequence of relations
					$projAttribute = new ProjectionAttribute();
					$projAttribute->setValue("'".$this->undefPathValues[$i][$j]."' AS UNDEF".$this->undefPathNames[$i][$j]);
					$projAttribute->setVisibility(true);
                                        $projAttribute->setSignificant(false);
					$projAttribute->setIsId(false);
					$projAttribute->setObjectName('');
					$projAttribute->setPropertyName('');
					$projAttribute->setPreferredName('UNDEF' . $this->undefPathNames[$i][$j]);
					array_push($selectAttributesGlobal, $projAttribute);
					
					//attribute for sequence of objects
					$projAttribute = new ProjectionAttribute();
					$projAttribute->setValue($this->undefPathObjects[$i][$j]." AS UNDEF_OBJ".$this->undefPathNames[$i][$j]);
					$projAttribute->setVisibility(true);
                                        $projAttribute->setSignificant(false);
					$projAttribute->setIsId(false);
					$projAttribute->setObjectName('');
					$projAttribute->setPropertyName('');
					$projAttribute->setPreferredName('UNDEF_OBJ' . $this->undefPathNames[$i][$j]);
					array_push($selectAttributesGlobal, $projAttribute);
				}
			}
			
			//get values from query construct
			$properties = $this->queries[$i]->getAllProperties();
			$objects =    $this->queries[$i]->getAllObjects();
			$conditions = $this->queries[$i]->getAllConditions();
			$relations =  $this->queries[$i]->getAllRelations();

			//make disjunctive list based on the local objects
			$this->loadDisjunctiveFromLocalObjects($this->queries[$i], $conn);
			
			//if it's only from external sources, it will not have a disjunctive list
			//so in this step I'll create one list with the most constrained actor
			if (sizeof($this->queueDisjunctive) == 0 && sizeof($objects) > 1) {
				$this->loadDisjunctiveForOnlyExternal($this->queries[$i], $conn);
			}
			
			//propagate 'narrowing' to the external actors
			if (sizeof($this->queueDisjunctive) > 0)	{
				$this->loadDisjunctiveFromNarrowing($this->queries[$i], $conn);
			}
			
			
			//process the whole query
			$finalQuery = new Query($objects, $properties, $relations, $conditions);
			$finalProcessor = new QueryProcessor();
			
			$includeOrder = true;
                        $localQueryMaxResults = $maxResults;

			if (sizeof($this->queries) > 1) {
				$localQueryMaxResults = 0;
				$includeOrder = false;
			}
			
			$sqlQuery = $finalProcessor->process($finalQuery, $this->disjunctive, $selectAttributesGlobal, $conn, $maxResults, $includeOrder, $i);
			array_push($this->sqlQueries, $sqlQuery);
			
			$this->selectAttributes = $finalProcessor->getSelectAttributes();
		}	
		
		
		//makes the union of the queries
		$unionQuery = "";
		for ($i=0; $i<sizeof($this->sqlQueries); $i++) {
			$unionQuery .= "(".$this->sqlQueries[$i].") UNION ";
		}
		$unionQuery = substr($unionQuery, 0, strlen($unionQuery)-6);
		
		
		if (sizeof($this->queries) > 1) {
			
			$orderBy = $this->getOrderClause($this->queries[0]->getAllProperties());
			
			$unionQuery .= $orderBy;
                        
                        if ($maxResults != 0) {
                            //limit number of results
                            if (DB::getDialect() == 'DB2') {
                            	$unionQuery .= " FETCH FIRST $maxResults ROWS ONLY";  //DB2
                            } else if (DB::getDialect() == 'Postgres') {
                                    $unionQuery .= " LIMIT ".$maxResults;     //Postgres
                            }
                        }
		}
		//echo $unionQuery;
		return $unionQuery;		
		//return array($unionQuery, $this->queries[0][1]);
		
	}
	
	
	/**
     * Find the paths between two types of objects at a maximum number of steps
     * @param int $startObjectId Object id of the starting object
     * @param int $endObjectId Object id of the ending object
     * @param int $maxDepth Maximum length of the path
     * @param link_resource $conn Database connection
     * @return array Array of Path objects
     */
	public function findPaths($startObjectId, $endObjectId, $maxDepth=4, $conn) {
		
		$startingPath = new Path();
		$startingPath->setObjectIds(array($startObjectId));
		$startingPath->addSignificantProperty($this->getSignificantProperty($startObjectId, $conn));
		$startingPath->setCurrentdepth(0);
		
		$queue = array($startingPath);
		
		$result = array();
		$counter = 0;
		
		while (sizeof($queue) > 0) {
			
			$counter++;
			$currentPath = array_shift($queue);
			
			if ($currentPath->getCurrentDepth() < $maxDepth) {
				
				$relDeduplication = array();
				$oneCardinality = array();
				
				//validate if relation exist
				$sql_query = 	"SELECT DISTINCT sociql_relation.id, sociql_relation.name, property1_fk, real_name1, 
												property2_fk, real_name2, actor_fk, cardinality, '2' AS num 
								FROM sociql_relation, sociql_property 
								WHERE sociql_relation.property2_fk = sociql_property.id AND 
									property1_fk IN (SELECT id FROM sociql_property WHERE actor_fk = " . $currentPath->getLastObjectId() . ") 
								UNION 
								SELECT DISTINCT sociql_relation.id, sociql_relation.name, property1_fk, real_name1, 
												property2_fk, real_name2, actor_fk, cardinality, '1' AS num 
								FROM sociql_relation, sociql_property 
								WHERE sociql_relation.property1_fk = sociql_property.id AND 
									property2_fk IN (SELECT id FROM sociql_property WHERE actor_fk = " . $currentPath->getLastObjectId() . ") AND direction = 2";
				
				$sql_result = DB::query($sql_query, $conn);
				
				while ($row = DB::fetchAssoc($sql_result)) {
						
					//if (!(in_array($row["id"], $relDeduplication))) {
						
						array_push($relDeduplication, $row["id"]);
						
						$indexAnchor = 		'1';
						$indexComplement = 	$row["num"];
						if ($indexComplement == '1') {
							$indexAnchor = '2';
						}
						
						$path = clone $currentPath;
						
						if ($counter == 1 && $row["cardinality"] == "1-1") {
							$path->addOneToOneRelation($startObjectId);
						}
						
						
						//get the query
						$sql_query = 	"SELECT query 
										FROM sociql_relation 
										WHERE id = ". $row["id"];
						
						$sql_result2 = DB::query($sql_query, $conn);
						
						if ($row2 = DB::fetchAssoc($sql_result2)) {
							$path->addQuery($row2["query"]);
						}
						
						$path->addObjectId($row["actor_fk"]);
						$path->addLeftPropertyId($row["property".$indexAnchor."_fk"]);
						$path->addLeftPropertyId($row["property".$indexComplement."_fk"]);
						$path->addRelationName($row["name"]);
						$path->addRelationId($row["id"]);
						$path->setCurrentDepth($path->getCurrentdepth() + 1);
						$path->addSignificantProperty($this->getSignificantProperty($row["actor_fk"], $conn));
						
						//discard path
						if (in_array($row["actor_fk"], $currentPath->getOneToOneRelations())) {
							$path->setOneToOneRelations(array());
							break;
						}
						
						if ($row["cardinality"] == "1-1") {
							$path->addOneToOneRelation($row["actor_fk"]);
						} else {
							$path->setOneToOneRelations(array());
						}
						
						if ($row["actor_fk"] == $endObjectId) {
							array_push($result, $path);
                                                        array_push($queue, $path);
						} else {
							array_push($queue, $path);
						}
					//}
				}
			}
		}
		
		return $result;
	}
	
	
	/**
     * Get significant property from an object type
     * @param int $actorId Object id 
     * @param link_resource $conn Database connection
     * @return array Significant property.  If there is a significant property, then
     * the first element in the array is <i>PROP_ID</i> and the second element is
     * the property id.  If there is no significant property, then the first element is
     * <i>OBJ_ID</i> and the second element is thename of the object id.
     */
	private function getSignificantProperty($actorId, $conn) {
		$significantProp = array();
		
		//get significant property
		$sql_query = 	"SELECT id
						FROM sociql_property 
						WHERE actor_fk = $actorId AND significant = 1";
		
		$sql_result = DB::query($sql_query, $conn);
		
		if ($row = DB::fetchAssoc($sql_result)) {
			$significantProp[0] = 'PROP_ID';
			$significantProp[1] = $row["id"];
		} else {
			//if there are no significant props., then look for the id
			$sql_query = 	"SELECT actor_id 
							FROM sociql_actor 
							WHERE id = $actorId";
			
			$sql_result = DB::query($sql_query, $conn);
			
			if ($row = DB::fetchAssoc($sql_result)) {
				$significantProp[0] = 'OBJ_ID';
				$significantProp[1] = $row["actor_id"];
			}		
		}
		
		return $significantProp;
	}
	
	
	/**
     * Evaluates if a given array of combinations has reached the end.
     * @param array $levels Array of current number of objects in the combination
     * @param array $max Array of maximum number of objects in the combination
     * @return boolean True if it has reached the maximum numbers.  False otherwise
     */
	private function endConditionCombination($levels, $max) {
		$flag = true;
		
		for ($i=0; $i<sizeof($levels); $i++) {
	
			if ($levels[$i] != $max[$i]-1) {
				$flag = false;
				break;
			}
		}
		
		return $flag;
	}
	
	
	/**
     * Creates a disjunctive list of ids, assuming the query has only external objects
     * @param Query $query Processed SociQL query
     * @param link_resource $conn Database connection
     */
	private function loadDisjunctiveForOnlyExternal($query, $conn) {
		
		$properties = $query->getAllProperties();
		$objects =    $query->getAllObjects();
		$conditions = $query->getAllConditions();
		$relations =  $query->getAllRelations();
		
		$targetObjectName = 	"";
		$targetObjectNum = 		0;
		$targetPropertyName = 	"";
		$targetPropertyId = 	"";
		
		$planProperties = 	array();
		$planObjects = 		array();
		$planConditions = 	array();
		$planRelations = 	array();
		
		$selectAttributes = array();
		
		//get the most constrained actor as the target
		foreach ($objects as $objectName=>$object) {
			$counter = 	0;
			$propName = "";
			$propId = 	"";
			
			if ($object->getSiteType() != "local" && $object->getSiteType() != "facebook") {
				
				if (isset($conditions[$localObject])) {
					foreach ($conditions[$localObject] as $propertyName=>$condSubgroup) {
						foreach ($condSubgroup as $condRef=>$condition) {	

							$propName = $propertyName;
							$propId = $condition->getLeftId();
							$counter++;
						}
					}
				}
			}
			
			if ($counter > $targetObjectNum) {
				$targetObjectName = $objectName;
				$targetObjectNum = $counter;
				$targetPropertyName = $propName;
				$targetPropertyId = $propId;
			} 
		}
		
		
		//if there is a candidate (external)
		if ($targetObjectName != "") {
			
			//try to create the disjunctive list
			//add actor
			$planObjects[$targetObjectName] = $objects[$targetObjectName];
			
			//select property
			$query = 	"SELECT name, real_name 
						FROM sociql_property 
						WHERE id = ".$targetPropertyId;
			$result = DB::query($query, $conn);
			
			if ($row = DB::fetchAssoc($result)) {
				
				if ($row["real_name"] != "") {
					$selectName = $targetObjectName . "_" . $targetPropertyName . "__." . $row["real_name"];
				
				} else {
					$selectName = $targetObjectName . "_" . $targetPropertyName . "__." . $row["name"];
				}
				
				$projAttribute = new ProjectionAttribute();
				$projAttribute->setValue($selectName);
				$projAttribute->setVisibility(true);
                                $projAttribute->setSignificant(false);
				$projAttribute->setIsId(false);
				$projAttribute->setObjectName($targetObjectName);
				$projAttribute->setPropertyName($targetPropertyName);
				array_push($selectAttributes, $projAttribute);
				//echo "<br>-->SELECT NAME = ".$selectName;
			}
			
			//conditions
			if (isset($conditions[$targetObjectName])) {
				$planConditions[$targetObjectName] = clone $conditions[$targetObjectName];
			}
			
			
			$planQuery = new Query($planObjects, $planProperties, $planRelations, $planConditions);
			
			$planProcessor = new QueryProcessor();
			$planSqlQuery = $planProcessor->process($planQuery, $this->disjunctive, $selectAttributes, $conn, 0, true);
			
			
			$sql_result = DB::query($planSqlQuery, $conn);
			$elements = array();

			while ($row = DB::fetchAssoc($sql_result)) {
				array_push($elements, $row[0]); 
			}
			
			$this->disjunctive[$targetObjectName][$targetPropertyId] = $elements;

			//add to queue
			array_push($this->queueDisjunctive, $targetObjectName . "." . $targetPropertyId);
		}
	} 
	
	/**
     * Creates a disjunctive list of ids, using only local objects
     * @param Query $query Processed SociQL query
     * @param link_resource $conn Database connection
     */
	private function loadDisjunctiveFromLocalObjects($query, $conn) {
		
		$properties = $query->getAllProperties();
		$objects =    $query->getAllObjects();
		$conditions = $query->getAllConditions();
		$relations =  $query->getAllRelations();
		
		foreach ($objects as $objectName=>$object)	{
			$objects[$objectName]->setNumberIds(0);
		}
		
		//make disjunctive list based on local subquery 
		foreach ($relations as $relationName=>$relGroup) {
			
			foreach ($relGroup as $relationPropName=>$relSubgroup) {
				
				foreach ($relSubgroup as $relationRef=>$relation) {
					
					if (($objects[$relation->getActorName1()]->getSiteType() != $objects[$relation->getActorName2()]->getSiteType()) && 
						($objects[$relation->getActorName1()]->getSiteType() == "local" || $objects[$relation->getActorName2()]->getSiteType() == "local")) {
						
						$planProperties = 	array();
						$planObjects = 		array();
						$planConditions = 	array();
						$planRelations = 	array();
						
						$selectAttributes = array();  
						
						//names used by the relation
						$relationId = 			$relation->getId();
						$localObject = 			$relation->getActorName1();
						$localObjectPropName = 	$relation->getPropertyName1();
						$localObjectPropId = 	$relation->getPropertyId1();
						$externalObject = 		$relation->getActorName2();
						$externalObjectPropName = $relation->getPropertyName2();
						$externalObjectPropId =   $relation->getPropertyId2();
						
						if ($objects[$relation->getActorName1()]->getSiteType() != "local") {
							
							$localObject = 			$relation->getActorName2();
							$localObjectPropName = 	$relation->getPropertyName2();
							$localObjectPropId = 	$relation->getPropertyId2();
							$externalObject = 		$relation->getActorName1();
							$externalObjectPropName = $relation->getPropertyName1();
							$externalObjectPropId =   $relation->getPropertyId1();
						}
						
						//get names used by entities involved in relation
						$externalPropertyName = $externalPropertyRealName = null;
						$localPropertyName = $localPropertyRealName = null;
						
						$query = 	"SELECT id, name, real_name
									FROM sociql_property 
									WHERE sociql_property.id = " . $relation->getPropertyId1() . " OR 
										sociql_property.id = " . $relation->getPropertyId2();
						
						$result = DB::query($query, $conn);
						
						while ($row = DB::fetchAssoc($result)) {
							
							if ($row["id"] == $localObjectPropId) {
								$localPropertyName = 	$row["name"];
								$localPropertyRealName= $row["real_name"];
							
							} else {
								$externalPropertyName = $row["name"];
								$externalPropertyRealName = $row["real_name"];
							}
						}

						
						//Start local query
						//add the interrelation, specifying only the part for the local
						$selectName = "";
						
						if ($relation->getQuery() != "") {
							
							$planRelations[$relationName][$relationPropName][$relationRef] = clone $relation;
							
							if ($relation->getActorName1() == $localObject) {
								$planRelations[$relationName][$relationPropName][$relationRef]->setActorName2(null);
								$selectName = $planRelations[$relationName][$relationPropName][$relationRef]->getPropertyName2();
							
							} else {
								$planRelations[$relationName][$relationPropName][$relationRef]->setActorName1(null);
								$selectName = $planRelations[$relationName][$relationPropName][$relationRef]->getPropertyName1();
							}
							
							$selectName = $relationName . "_" . $relationPropName . "_" . $relationRef . "." . $selectName;
							
							//add the attribute in select
							$projAttribute = new ProjectionAttribute();
							$projAttribute->setValue($selectName);
							$projAttribute->setVisibility(true);
                                                        $projAttribute->setSignificant(false);
							$projAttribute->setIsId(false);
							$projAttribute->setObjectName($relationName);
							$projAttribute->setPropertyName($relationPropName);
							array_push($selectAttributes, $projAttribute);
                                                        
						} else {
							$planProperties[$localObject][$localPropertyName] = $properties[$localObject][$localPropertyName];
						}
						
						$queue = array($localObject);
                                                $used = array();
						while (sizeof($queue) > 0) {
							
							$currentObject = array_pop($queue);
							//add actor to correlated
							$planObjects[$currentObject] = $objects[$currentObject];
												
							//conditions
							if (isset($conditions[$currentObject])) {
                                                            foreach ($conditions[$currentObject] as $condPropertyName=>$condSubgroup) {
                                                                    foreach ($condSubgroup as $condRef=>$condition) {
                                                                        $planConditions[$currentObject][$condPropertyName][$condRef] = clone $condition;
                                                                    }
                                                            }
							}
							
							//relations
							foreach ($relations as $relationName2=>$relGroup2) {

								foreach ($relGroup2 as $relationPropName2=>$relSubgroup2) {
				
									foreach ($relSubgroup2 as $relationRef2=>$relation2) {
								
										if (($relation2->getActorName1() == $currentObject || $relation2->getActorName2() == $currentObject) &&
											($objects[$relation2->getActorName1()]->getSiteType() == $objects[$relation2->getActorName2()]->getSiteType())) {
											
											//echo "<br>---> add $key2";
											$planRelations[$relationName2][$relationPropName2][$relationRef2] = clone $relation2;
											
											//add the complementary actor
											$complementObject = $relation2->getActorName1();
											if ($currentObject == $complementObject) {
												$complementObject = $relation2->getActorName2();
											}
											
											if (!(in_array($complementObject, $used))) {
												//add the complementary actor
												array_push($queue, $complementObject);
											}
										}
									}
								}
							}
							
							
							array_push($used, $currentObject);
						}
						
						
						
						$planQuery = new Query($planObjects, $planProperties, $planRelations, $planConditions);
						
						$planProcessor = new QueryProcessor();
						$planSqlQuery =  $planProcessor->process($planQuery, $this->disjunctive, $selectAttributes, $conn, 0, true);
						
						//execute the queries and store in disjunctive for the external actor
						$elements = array();
                                                
						$sql_result = DB::query($planSqlQuery, $conn);
						
						while ($row = DB::fetchArray($sql_result)) {
							array_push($elements, $row[0]);
						}
						
						$this->disjunctive[$externalObject][$externalObjectPropId] = $elements;
	                                        
						//add to queue
						array_push($this->queueDisjunctive, $externalObject . "." . $externalObjectPropId);
					}
				}
			}
		}
	}
		
	
	/**
     * Creates a disjunctive list of ids, from narrowing using the existing disjunctive lists.
     * @param Query $query Processed SociQL query
     * @param link_resource $conn Database connection
     */
	private function loadDisjunctiveFromNarrowing($query, $conn) {
		
		$properties = $query->getAllProperties();
		$objects =    $query->getAllObjects();
		$conditions = $query->getAllConditions();
		$relations =  $query->getAllRelations();
	
		do {
					
            $queueElement = array_pop($this->queueDisjunctive);
			$key_split = explode(".", $queueElement);
			$objectName = trim($key_split[0]);
			//$val = $queue_disjunctive[$key];
                                        //$val = $queue_element[$key];
			//echo "<br>disj = ".$key;
			
			foreach ($relations as $relationName=>$relGroup) {

				foreach ($relGroup as $relationPropName=>$relSubgroup) {

					foreach ($relSubgroup as $relationRef=>$relation) {
						
						//look the relations with the actor that has a disjunctive list
						if ($objectName == $relation->getActorName1() || $objectName == $relation->getActorName2()) {
							
							//if the relation is between two of the same site (external)
							if ($objects[$relation->getActorName1()]->getSiteType() == $objects[$relation->getActorName2()]->getSiteType()) {
								
								//current is the actor with the disjunctive list
								//complement is the actor we want to propagate the disj. list
								$complementProperty = 	$complementPropertyName = 	$complementObjectId = 	$complementObjectName = null;
								$currentProperty = 		$currentPropertyName = 		$currentObjectId  = 	$currentObjectName = 	null;
							
								//get information about the properties in relation
								$query = 	"SELECT sociql_property.id, sociql_property.name, sociql_property.actor_fk 
											FROM sociql_property, sociql_actor, sociql_site 
											WHERE actor_fk = sociql_actor.id AND site_fk = sociql_site.id AND 
												 (sociql_property.id = " . $relation->getPropertyId1() . " 
											 	  OR sociql_property.id = " . $relation->getPropertyId1() . ")";
						
								$result = DB::query($query, $conn);
								
								while ($row = DB::fetchAssoc($result)) {
									
									if ($objects[$objectName]->getId() != $row["actor_fk"]) {
										$complementProperty = 		$row["id"];
										$complementPropertyName = 	$row["name"];
										$complementObjectId = 		$row["actor_fk"];
									
									} else {
										$currentProperty = 		$row["id"];
										$currentPropertyName = 	$row["name"];
										$currentObjectId = 		$row["actor_fk"];
									}
									
									
									if ($objects[$relation->getActorName1()]->getId() == $currentObjectId) {
										$currentObjectName = 	$relation->getActorName1();
										$complementObjectame = 	$relation->getActorName2();
									
									} else if ($objects[$relation->getActorName2()]->getId() == $currentObjectId) { 
										$currentObjectName = 	$relation->getActorName2();
										$complementObjectame = 	$relation->getActorName1();
									}
								}
								
								
								$found = false;
								
								$planProperties = 	array();
								$planObjects = 		array();
								$planConditions = 	array();
								$planRelations = 	array();
								
								$selectAttributes = array();
								
								//if I have to make a query to know the disj. list (only if it is a N-N relation)
								if ($relation->getQuery() != "") {
											
									$selectName = "";
									
									//have to create the array through a query
									//add actor
									$planObjects[$currentObjectName] = $objects[$currentObjectName];
									
									//select property
									$planProperties[$currentObjectName][$currentPropertyName] = $properties[$currentObjectName][$currentPropertyName];
									
									//conditions
									if (isset($conditions[$currentObjectName])) {
										$planConditions[$currentObjectName] = clone $conditions[$currentObjectName];
									}
									
									
									$planQuery = new Query($planObjects, $planProperties, $planRelations, $planConditions);
			
									$planProcessor = new QueryProcessor();
									$planSqlQuery = $planProcessor->process($planQuery, $this->disjunctive, $selectAttributes, $conn, 0, true);
									
									$sql_result = DB::query($planSqlQuery, $conn);
									$elements = array();
				
									while ($row = DB::fetchAssoc($sql_result)) {
										array_push($elements, $row[0]); 
									}
									
									$this->disjunctive[$relationName][$currentProperty] = $elements;
	
									//add to queue
									array_push($this->queueDisjunctive, $relationName.".".$complementProperty);
									
									
									//FOR COMPLEMENT ACTOR	
									$planProperties = 	array();
									$planObjects = 		array();
									$planConditions = 	array(); 
									$planRelations = 	array();
									
									$selectAttributes = array();							
									
									//add relation
									$planRelations[$relationName][$relationPropName][$relationRef] = $relation;
									
									
									//change query if it's facebook and has a disjunctive alternative
									$sql_query = 	"SELECT fb_disj_query 
													FROM sociql_relation 
													WHERE id = " . $relation->getId();
									
									$sql_result = DB::query($sql_query, $conn);
									
									if ($row = DB::fetchAssoc($sql_result)) {
										
										if ($row["fb_disj_query"] != "") {
											
											$planRelations[$relationName][$relationPropName][$relationRef]->setQuery($row["fb_disj_query"]);
											$relations[$relationName][$relationPropName][$relationRef]->setQuery($row["fb_disj_query"]);
										}
									}
									
									
									$tmpIndex = 0;
									if ($objects[$relation->getActorName1()]->getId() == $currentObject) {
										$tmpIndex = 2;	
									} else {
										$tmpIndex = 1;
									}
									
									if ($tmpIndex != 0) {
										
										$planRelations[$relationName][$relationPropName][$relationRef]->setActorName(null, $tmpIndex);
										
										if ($relation->getPropertyName($tmpIndex) != "") {
											$selectName = $relation->getPropertyName($tmpIndex);
										
										} else {
											$selectName = $complementPropertyName;
											$planRelations[$relationName][$relationPropName][$relationRef]->setPropertyName($complementPropertyName, $tmpIndex);
										}
										
										if ($tmpIndex == 2) {
											$tmpIndex = 1;
										} else {
											$tmpIndex = 2;
										}
										
										if ($relation->getPropertyName($tmpIndex) == "") {
											$planRelations[$relationName][$relationPropName][$relationRef]->setPropertyName($currentPropertyName, $tmpIndex);
										}
									}
									
									$selectName = $relationName . "_" . $relationPropName . "_" . $relationRef . "." . $selectName;
									
									//add the attribute in select
									$projAttribute = new ProjectionAttribute();
									$projAttribute->setValue($selectName);
									$projAttribute->setVisibility(true);
                                                                        $projAttribute->setSignificant(false);
									$projAttribute->setIsId(false);
									$projAttribute->setObjectName($relationName);
									$projAttribute->setPropertyName($relationPropName);
									array_push($selectAttributes, $projAttribute);
									
									$planProperties[$currentObjectName][$currentPropertyName] = $properties[$currentObjectName][$currentPropertyName];
									
									//add actor
									$planObjects[$currentObjectName] = $objects[$currentObjectName];
									
									//conditions
									if (isset($conditions[$currentObjectName])) {
										$planConditions[$currentObjectName] = clone $condition[$currentObjectName];
									}
									
									
									$planQuery = new Query($planObjects, $planProperties, $planRelations, $planConditions);
			
									$planProcessor = new QueryProcessor();
									$planSqlQuery = $planProcessor->process($planQuery, $this->disjunctive, $selectAttributes, $conn, 0, true);
									
									$sql_result = DB::query($planSqlQuery, $conn);
									$elements = array();
				
									while ($row = DB::fetchAssoc($sql_result)) {
										array_push($elements, $row[0]); 
									}
									
									$this->disjunctive[$complementObjectName][$complementProperty] = $elements;
									
								
								} else if (!$found && $relation->getQuery() == "") {
									//relations N-1, 1-1
									if (isset($this->disjunctive[$currentObjectName][$currentProperty])) {
										$this->disjunctive[$complementObjectName][$complementProperty] = $this->disjunctive[$currentObjectName][$currentProperty];
										
										//add to queue
										//array_push($queue_disjunctive, $complement_actor_name.".".$complement_prop);
									}
									else
									{
										//have to create the array through a query
										//add actor
										$planObjects[$currentObjectName] = $objects[$currentObjectName];
										
										//select property
										$planProperties[$currentObjectName][$currentPropertyName] = $properties[$currentObjectName][$currentPropertyName];
										//echo "<br>////add ".$current_actor_name.".".$current_prop_name;
										
										//conditions
										if (isset($conditions[$currentObjectName])) {
											$planConditions[$currentObjectName] = $conditions[$currentObjectName];
										}
										

										$planQuery = new Query($planObjects, $planProperties, $planRelations, $planConditions);
			
										$planProcessor = new QueryProcessor();
										$planSqlQuery = $planProcessor->process($planQuery, $this->disjunctive, $selectAttributes, $conn, 0, true);
				
										
										$sql_result = DB::query($planSqlQuery, $conn);
										$elements = array();
					
										while ($row = DB::fetchAssoc($sql_result)) {
											array_push($elements, $row[0]);
										}
										
										$this->disjunctive[$complementObjectName][$complementProperty] = $elements;
										
										//add to queue
										array_push($this->queueDisjunctive, $complementObjectName . "____." . $complementProperty);
										
									}
								}
							}
						}			
					}
				}
			}
			
		} while(sizeof($this->queueDisjunctive) > 0);
	}
	
	
	/**
     * Get the SQL Order clause
     * @param array $properties Array of property objects
     * @return string SQL Order clause
     */
	private function getOrderClause($properties) {
		//order when unions		
		$counter = 0;
		$orderBy = " ORDER BY ";
		
		foreach ($this->selectAttributes as $keyName=>$projectionAttr) {
			$counter++;
			
			$selectValue = $projectionAttr->getValue();
			$keySplit = explode(".", $selectValue);
                                
			$propertyRealName = "";
			if (isset($keySplit[1])) {
            	$propertyRealName = $keySplit[1];
            }
                                
			$objectName = 	$projectionAttr->getObjectName();
			$propertyName = $projectionAttr->getPropertyName();
			
			foreach ($properties as $propObjectName=>$propertiesGroup) {
				
				foreach ($propertiesGroup as $propPropertyName=>$property) {
					
					$propertyRealName2 = $propPropertyName;
					$found = $visible = false;
					
					if ($property->getRealName() != "") {
						$propertyRealName2 = $property->getRealName();
					}
					
					
					if ($objectName == $propObjectName && $propertyName == $propPropertyName && $propertyRealName == $propertyRealName2) {
						$found = true;
						
						if ($property->isVisible() && $property->isSortable()) {
							$orderBy .= $counter . ", ";
						}
						
						break;
					}
				}
			}
			
		}
		
		if ($orderBy != " ORDER BY ") {
			$orderBy = substr($orderBy, 0, sizeof($orderBy)-3);
			
		} else {
			$orderBy = "";
		}
		
		return $orderBy;
	}
	
	
	/**
     * Creates queries from undefined paths.
     * @param Query $query Processed SociQL query
     * @param array $undefPaths Array of paths created by undef relationships
     * @param array $undefNames
     * @param link_resource $conn Database connection 
     */
	private function createQueriesFromUndef($query, $undefPaths, $undefNames, $conn) {
		//array to create combinations
		$levels = array();
		$max = array();
		
		for ($i=0; $i<sizeof($undefPaths); $i++) {
			$levels[$i] = -1;
			$max[$i] = sizeof($undefPaths[$undefNames[$i]]); 
		}
		
		$counter = 0;
		
		//iterate over all the possible combinations
		while (!($this->endConditionCombination($levels, $max))) {
			
			//get values from query construct
			$properties =	$query->getAllProperties();
			$objects = 		$query->getAllObjects();
			$conditions = 	$query->getAllConditions();
			$relations = 	$query->getAllRelations();
			
			//array to store the combination of paths
			$tmpRel = array();
			
			//add the first path 0 - 0 - 0 ...
			if ($counter == 0) {
				
				for ($i=0; $i<sizeof($levels); $i++) {
					$levels[$i] = 0;
					$tmpRel[$undefNames[$i]] = $undefPaths[$undefNames[$i]][$levels[$i]];
				}
			
			} else {
				
				//controls the path iteration on every UNDEF rel
				for ($i=0; $i<sizeof($levels); $i++) {
					
					if ($i == sizeof($levels)-1) {
						$levels[$i] = ($levels[$i] + 1) % sizeof($undefPaths[$undefNames[$i]]);
					}
					
                                               
					if (isset($undefNames[$i+1]) && isset($undefPaths[$undefNames[$i+1]])) {

						if ($levels[$i+1] == sizeof($undefPaths[$undefNames[$i+1]])-1) {
							
							$change = true;

							for ($j=$i+2; $j<sizeof($levels); $j++) {
								
								if ($levels[$j] != sizeof($undefPaths[$undefNames[$j]])-1) {
									$change = false;
								}
							}

                                if ($change) {
								$levels[$i] = ($levels[$i] + 1) % sizeof($undefPaths[$undefNames[$i]]);
							}
						}
					}
					
					$tmpRel[$undefNames[$i]] = $undefPaths[$undefNames[$i]][$levels[$i]];
				}
			}
			
			
			$undefNames = array();
			$undefValues = array();
			$undefObjects = array();
			$q = clone $query;
			
			//from the tmpRel, change the query
			foreach ($tmpRel as $name=>$path) {
				
				$undefPath = $path;
				
				$concatObjects = "";
                                if (DB::getDialect() == 'MySQL') {
                                    $concatObjects = "CONCAT(";
                                }
				
				array_push($undefNames,  $name);
				array_push($undefValues, implode(", ", $path->getRelationNames()));
				
				$lastObjectName = $relations["UNDEF"]["_"][$name]->getActorName1();
				
				for ($j=0; $j<sizeof($undefPath->getRelationIds()); $j++) {
					
					//create property
					$significantProp = $undefPath->getSignificantProperty($j);
					$concatObjects .= $this->getSignificantProjection($significantProp[0], $significantProp[1], $lastObjectName, $q, $conn);
                                        
                                        if (DB::getDialect() == 'DB2') {
                                            $concatObjects .= ' || \' - \' || ';
                                        } else if (DB::getDialect() == 'MySQL') {
                                            $concatObjects .= ", ' - ', ";
                                        }
							
					$objectName = "";
					
					if ($j < sizeof($undefPath->getRelationIds())-1) {
						//create actor
						$object = $this->createObject($undefPath->getObjectId($j+1), $conn);
						$object->setUndefObject(true);
						$objectName = $q->addObject($object);	
					} else {
						$objectName = $relations["UNDEF"]["_"][$name]->getActorName2();
					}
					
					$relation = $this->createRelation($undefPath->getRelationId($j), $lastObjectName, $objectName, 
									$undefPath->getLeftPropertyId(2*$j), $undefPath->getQuery($j), $conn);
														
					if ($relation != null) {
						$q->addRelation($relation);
					}
					
					$lastObjectName = $objectName;
				}
				
				
				$significantProp = $undefPath->getSignificantProperty(sizeof($undefPath->getRelationIds()));
				$concatObjects .= $this->getSignificantProjection($significantProp[0], $significantProp[1], $lastObjectName, $q, $conn);
                                
                                if (DB::getDialect() == 'MySQL') {
                                    $concatObjects .= ")";
                                }
                                
				array_push($undefObjects, $concatObjects);
			}
			
		
			array_push($this->undefPathNames, $undefNames);
			array_push($this->undefPathValues, $undefValues);
			array_push($this->undefPathObjects, $undefObjects);
			array_push($this->queries, $q);
			$counter++;
			
		}
	}
	
	
	/**
     * Gets a SQL field of the significant property in an UNDEF path
     * @param string $significantType Type of significant property
     * @param string $significantValue Value of the significant property
     * @param string $objectName Name of the object
     * @param Query $query Processed SociQl query
     * @param link_resource $conn Database connection
     * @return string SQL field of the significant property  
     */
	private function getSignificantProjection($significantType, $significantValue, $objectName, $query, $conn) {
		
		$concatObjects = "";
		
		//get values from query construct
		$properties =	$query->getAllProperties();
		$conditions = 	$query->getAllConditions();
                $objects = 	$query->getAllObjects();
		
		if ($significantType == 'PROP_ID') {
			$property = $this->createProperty($significantValue, false, false, $conn);
			
			$sql_query = 	"SELECT name, real_name
							FROM sociql_property 
							WHERE id = $significantValue";
			$sql_result = DB::query($sql_query, $conn);
			
			if ($row = DB::fetchAssoc($sql_result)) {
				
				if (!isset($properties[$objectName][$row["name"]])) {
					$query->addNamedProperty($objectName, $row["name"], $property);
				}
				
				//update Significant Property
				$realName = $row["name"];
				if ($row["real_name"] != "") {
					$realName = $row["real_name"];
				}
				$concatObjects .= $objectName . '_' . $row["name"] . '__.' . $realName;
			}
			
		} else if ($significantType == 'OBJ_ID') {
			
			$found = false;
			
			//search for candidate property to link the id
			foreach ($properties as $propObjectName=>$propertiesGroup) {
				foreach ($propertiesGroup as $propPropertyName=>$property) {
					if ($propObjectName == $objectName) {
						$concatObjects .= $propObjectName . '_' . $propPropertyName . '__.' . $significantValue;
						$found = true; 
					}
				}
			}		
			
			//search for candidate condition to link the id
			if (!$found) {
				foreach ($conditions as $condObjectName=>$condGroup) {
					foreach ($condGroup as $condPropertyName=>$condSubgroup) {
						foreach ($condSubgroup as $condRef=>$condition) {
							if ($propObjectName == $objectName) {
								$concatObjects .= $condObjectName . '_' . $condPropertyName . '_' . $condRef . '.' . $significantValue;
								$found = true; 
							}	
						}
					}
				}
			}
			
			//search for candidate object to link the id
			if (!$found) {
				foreach ($objects as $objObjectName=>$propertiesGroup) {
					if ($objObjectName == $objectName) {
						$concatObjects .= $objObjectName . '____.' . $significantValue;
						$found = true; 
					}
				}
			}
		}
		
		return $concatObjects;
	}
	
	
	/**
     * Create an object
     * @param int $objectId Object id
     * @param link_resource $conn Database connection
     * @return Object Object  
     */
	private function createObject($objectId, $conn) {
		//create actor
		$object = null;
		
		//validate if actor exists
		$sql_query = 	"SELECT sociql_actor.id, sociql_actor.name, query, site_fk, actor_id, 
							url_required_prop, url, type, sociql_actor.real_name 
						FROM sociql_actor, sociql_site 
						WHERE sociql_actor.site_fk = sociql_site.id AND sociql_actor.id = $objectId" ;
		
		$sql_result = DB::query($sql_query, $conn);

		if ($row = DB::fetchAssoc($sql_result)) {
			
			$object = new Object();
			
			$object->setId($row["id"]);
			$object->setQuery($row["query"]);
			$object->setSiteId($row["site_fk"]);
			$object->setNameActorId($row["actor_id"]);
			$object->setRequiredProps(explode(",", $row["url_required_prop"]));
			$object->setNumberIds(0);
			$object->setBaseUrl($row["url"]);
			$object->setSiteType($row["type"]);
			$object->setRealName($row["real_name"]);
		
		} else {
			throw new Exception("<strong>ERROR:</strong> Actor does not exist");
			$error = true;
			break;
		}
		
		return $object;
	}
	
	
	/**
     * Create a relation
     * @param int $relationId Relation id
     * @param string $objName1 Object name of first object
     * @param string $objName2 Object name of second object
     * @param int $propId1 Property id of first object
     * @param string $relationQuery Query of the relation
     * @param link_resource $conn Database connection
     * @return Relation Relation
     */
	private function createRelation($relationId, $objName1, $objName2, $propId1, $relationQuery, $conn) {
		
		$relation = null;
		
		//create relation
		$sql_query = 	"SELECT property1_fk, property2_fk, real_name1, real_name2 
						FROM sociql_relation 
						WHERE id = $relationId";
			
		$sql_result = DB::query($sql_query, $conn);

		if ($row = DB::fetchAssoc($sql_result)) {
			
			$relation = new Relation();
			
			$relation->setActorName1($objName1);
			$relation->setActorName2($objName2);
			
			if ($row["property1_fk"] == $propId1) {
				
				$relation->setPropertyName1($row["real_name1"]);
				$relation->setPropertyName2($row["real_name2"]);
				$relation->setPropertyId1($row["property1_fk"]);
				$relation->setPropertyId2($row["property2_fk"]);
			
			} else {
				
				$relation->setPropertyName1($row["real_name2"]);
				$relation->setPropertyName2($row["real_name1"]);
				$relation->setPropertyId1($row["property2_fk"]);
				$relation->setPropertyId2($row["property1_fk"]);
				
			}
			
			$relation->setQuery($relationQuery);
			$relation->setId($relationId);
		}
		else
		{
			throw new Exception("<strong>ERROR:</strong> Relation does not exist");
			$error = true;
			break;
		}
		
		return $relation;
	}
	
	
	/**
     * Create a property
     * @param int $propertyId Property id
     * @param boolean $visibility Is it visible in the result?
     * @param boolean $inProjection Is it included in the projection?
     * @param link_resource $conn Database connection
     * @return Property Property
     */
	private function createProperty($propertyId, $visibility, $inProjection, $conn) {
		
		$property = null;
		
		$sql_query = 	"SELECT id, name, query, real_name, sortable, significant, queriable 
						FROM sociql_property 
						WHERE id = $propertyId";
		$sql_result = DB::query($sql_query, $conn);
		
		if ($row = DB::fetchAssoc($sql_result)) {
			
			if ($row["queriable"] == 1) {
				
				$property = new Property();
				
				$property->setId($row["id"]);
				$property->setQuery($row["query"]);
				$property->setRealName($row["real_name"]);
				$property->setSortable(ParserSociQL::numToBoolean($row["sortable"]));
				$property->setSignificant(ParserSociQL::numToBoolean($row["significant"]));
				$property->setVisible($visibility);
				$property->setProjection($inProjection);
				
			}
			else {
				return ParserSociQL::raiseValidationError("Property " . $row["name"]. "can not be queried");
			}
		
		}
		
		return $property;
			
	}
	
	
	/**
     * Resolves the ontology elements in the query
     * @param Query $query Processed SociQL query
     * @param link_resource $conn Database connection
     * @return array Array of SociQL queries generated from ontology elements 
     */
	private function getOntologyQueries($query, $conn) {
		
		$properties =	$query->getAllProperties();
		$objects = 	$query->getAllObjects();
		$conditions = 	$query->getAllConditions();
		$relations = 	$query->getAllRelations();
		
		$ontArtifacts = array();
		$ontArtifactsName = array();
		
		//get ontology objects
		foreach ($objects as $objectName=>$object) {
			if ($object->getType() == 'ONTOLOGY') {
				array_push($ontArtifacts, $object);
				array_push($ontArtifactsName, $objectName);	
			}
		}
		
		//get ontology relationships
		foreach ($relations as $relationName=>$relGroup) {
			
			foreach ($relGroup as $relationPropName=>$relSubgroup) {
				
				foreach ($relSubgroup as $relationRef=>$relation) {
					
					if ($relation->getType() == 'ONTOLOGY') {
						array_push($ontArtifacts, $relation);
						array_push($ontArtifactsName, $relationName."&".$relationPropName."&".$relationRef);	
					}
				}
			}
		}
		
		
		//array to create combinations
		$levels = array();
		$max = array();
		
		for ($i=0; $i<sizeof($ontArtifacts); $i++) {
			$levels[$i] = -1;
			
			$numOntOptions = 0;
					
			if ($ontArtifacts[$i] instanceof Object) {
				$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyObjectIds());
			} else if ($ontArtifacts[$i] instanceof Relation) {
				$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyRelationIds());
			}
			
			$max[$i] = $numOntOptions;
		}
		
		$ontQueries = array();
		$counter = 0;
		$ontCounter = 0;
		$propSignificant = array();

		//iterate over all the possible combinations
		while (!($this->endConditionCombination($levels, $max))) {
			
			//add the first path 0 - 0 - 0 ...
			if ($counter == 0) {

				for ($i=0; $i<sizeof($ontArtifacts); $i++) {
					$levels[$i] = 0;
					
					$numOntOptions = 0;
					
					if ($ontArtifacts[$i] instanceof Object) {
						$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyObjectIds());
					} else if ($ontArtifacts[$i] instanceof Relation) {
						$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyRelationIds());
					}
					
				}
			
			} else {
				
				//controls the path iteration on every UNDEF rel
				for ($i=0; $i<sizeof($ontArtifacts); $i++) {

					if ($i == sizeof($levels)-1) {
						
						$numOntOptions = 0;
					
						if ($ontArtifacts[$i] instanceof Object) {
							$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyObjectIds());
						} else if ($ontArtifacts[$i] instanceof Relation) {
							$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyRelationIds());
						}
						
						$levels[$i] = ($levels[$i] + 1) % $numOntOptions;
					}
					
					$numOntOptions = 0;
					if (isset($ontArtifacts[$i+1])) {
						if ($ontArtifacts[$i+1] instanceof Object) {
							$numOntOptions = sizeof($ontArtifacts[$i+1]->getOntologyObjectIds());
						} else if ($ontArtifacts[$i+1] instanceof Relation) {
							$numOntOptions = sizeof($ontArtifacts[$i+1]->getOntologyRelationIds());
						}
						
						if ($levels[$i+1] == $numOntOptions-1) {
							
							$change = true;

							for ($j=$i+2; $j<sizeof($levels); $j++) {

								$curNumOntOptions = 0;
								if ($ontArtifacts[$j] instanceof Object) {
									$curNumOntOptions = sizeof($ontArtifacts[$j]->getOntologyObjectIds());
								} else if ($ontArtifacts[$j] instanceof Relation) {
									$curNumOntOptions = sizeof($ontArtifacts[$j]->getOntologyRelationIds());
								}
								
								if ($levels[$j] != $curNumOntOptions-1) {
									$change = false;
								}
							}

                                                        if ($change) {
                                                            $numOntOptions = 0;
								if ($ontArtifacts[$i] instanceof Object) {
									$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyObjectIds());
								} else if ($ontArtifacts[$i] instanceof Relation) {
									$numOntOptions = sizeof($ontArtifacts[$i]->getOntologyRelationIds());
								}
								$levels[$i] = ($levels[$i] + 1) % $numOntOptions;
							}
						}
						
					}
				}
			}
			
			$q = new Query();
			$discard = false;
			
			//add network objects
			foreach ($objects as $objectName=>$object) {
				if ($object->getType() == 'NETWORK') {
					$tmpObject = clone $object;
					$q->addNamedObject($objectName, $tmpObject);
				}
			}
			
			//add network relations
			foreach ($relations as $relationName=>$relGroup) {
				foreach ($relGroup as $relationPropName=>$relSubgroup) {
					foreach ($relSubgroup as $relationRef=>$relation) {
						if ($relation->getType() == 'NETWORK') {
							$tmpRelation = clone $relation;
							$q->addNamedRelation($relationName, $relationPropName, $relationRef, $tmpRelation);	
						}
					}
				}
			}
				
				
			for ($i=0; $i<sizeof($ontArtifacts); $i++) {
				if ($ontArtifacts[$i] instanceof Object) {
					//create actor
					$objIds = $ontArtifacts[$i]->getOntologyObjectIds();
					$object = $this->createObject($objIds[$levels[$i]], $conn);
				
					$q->addNamedObject($ontArtifactsName[$i], $object);
					//echo "<BR>ADD OBJECT = " . $ontArtifactsName[$i];
					
				} else if ($ontArtifacts[$i] instanceof Relation) {
					
					$relIds = $ontArtifacts[$i]->getOntologyRelationIds();
					
					$sql_query = 	"SELECT query, property1_fk, property2_fk
									FROM sociql_relation
									WHERE id = ".$relIds[$levels[$i]];
					
					$sql_result = DB::query($sql_query, $conn);
					
					if ($row = DB::fetchAssoc($sql_result)) {
						$firstObjectName = $ontArtifacts[$i]->getActorName1();
						$secondObjectName = $ontArtifacts[$i]->getActorName2();
						$relationQuery = $row["query"];
						$leftPropId = $row["property1_fk"];
						$rightPropId = $row["property2_fk"];
						$leftObjectId = null;
						$rightObjectId = null;
						
						$sql_query = 	"SELECT id, actor_fk
                                                                FROM sociql_property
                                                                WHERE id = ".$leftPropId." OR id = ".$rightPropId;
                                                
						$sql_result = DB::query($sql_query, $conn);
						
						while ($row = DB::fetchAssoc($sql_result)) {
							if ($row["id"] == $leftPropId) {
								$leftObjectId = $row["actor_fk"]; 
							} else if ($row["id"] == $rightPropId) {
								$rightObjectId = $row["actor_fk"]; 
							}
						}
						
						$firstObject = $q->getObject($firstObjectName);
						$secondObject = $q->getObject($secondObjectName);
						
						if ($leftObjectId == $firstObject->getId() && $rightObjectId == $secondObject->getId()) {
							
						} else if ($leftObjectId == $secondObject->getId() && $rightObjectId == $firstObject->getId()) {
							$tmp = $firstObjectName;  
							$firstObjectName = $secondObjectName;
							$secondObjectName = $tmp;
						} else {
							//ERROR
							//echo "<BR><STRONG>DISCARD</STRONG> ERROR CON $firstObjectName $leftObjectId ( ".$firstObject->getId()." ) Y $secondObjectName $rightObjectId ( ". $secondObject->getId(). ")";
							$discard = true;
							break;
							//throw new Exception("ERROR: Incompatible relation");
						}
						
						$relation = $this->createRelation($relIds[$levels[$i]], $firstObjectName, $secondObjectName, 
											$leftPropId, $relationQuery, $conn);
							
					}
					
					
					if ($relation != null) {
						//echo "<BR>ADD RELATION = " . $ontArtifactsName[$i];
						$namePieces = explode("&", $ontArtifactsName[$i]);
						$q->addNamedRelation($namePieces[0], $namePieces[1], $namePieces[2], $relation);
					}
				}
			}
			
			
			if (!$discard) {
				
				$propCount = 0;
				
				//add properties
				foreach ($properties as $propObjectName=>$propertiesGroup) {
					foreach ($propertiesGroup as $propPropertyName=>$property) {
						
						if ($objects[$propObjectName]->getType() == 'NETWORK') {
							$tmpProperty = clone $property;
							$q->addNamedProperty($propObjectName, $propPropertyName, $tmpProperty);
							
						} else if ($objects[$propObjectName]->getType() == 'ONTOLOGY') {
							
							$sql_query = 	"SELECT id, name
                                                                        FROM sociql_property
                                                                        WHERE ont_property = ".$property->getOntologyPropertyId()." AND actor_fk = ".$q->getObject($propObjectName)->getId();
							
							$sql_result = DB::query($sql_query, $conn);
							
							if ($row = DB::fetchAssoc($sql_result)) {
											
								$newProperty = $this->createProperty($row["id"], true, true, $conn);
								$newProperty->setPreferredName($property->getPreferredName());
								
								if ($ontCounter == 0) {
									array_push($propSignificant, $newProperty->isSignificant());
								} else {
									if (!(isset($propSignificant[$propCount])) || !($propSignificant[$propCount] == $newProperty->isSignificant())) {
										throw new Exception("The properties are inconsistent between themselves in terms of significance");
									}
								} 
								$propCount++;
								
								//check the properties required for significant select properties (for url)
								if ($newProperty != null) {
									
									$requiredProps = $objects[$propObjectName]->getRequiredProps();
									
									if ($newProperty->isSignificant() && sizeof($requiredProps) > 0) {
										
										//look for all the required props for url
										for ($j=0; $j<sizeof($requiredProps); $j++) {
											
											$additionalProperty = trim($requiredProps[$j]);
											
											//if the property has not been requested, then include it (but invisible)
											if ($additionalProperty != "" && $q->getProperty($propObjectName, $additionalProperty) == null) {
												
												//validate if property exists
												$sql_query = 	"SELECT id 
                                                                                                                FROM sociql_property
                                                                                                                WHERE actor_fk = " . $objects[$propObjectName]->getId() . " AND name = '$additionalProperty'";
												$sql_result2 = DB::query($sql_query, $conn);
												
												if ($row2 = DB::fetchAssoc($sql_result2)) {
													$newAdditionalProperty = $this->createProperty($row2["id"], false, true, $conn);					
													
													if ($ontCounter == 0) {
														array_push($propSignificant, $newAdditionalProperty->isSignificant());
													} else {
														if (!(isset($propSignificant[$propCount])) || !($propSignificant[$propCount] == $newAdditionalProperty->isSignificant())) {
															throw new Exception("The properties are not consistent between themselves in terms of significance");
														}
													}
													$propCount++;
													
													if ($newAdditionalProperty != null) {
														//echo "<BR>ADD PROPERTY ++ = " . $propObjectName . " - " . $additionalProperty;
														$q->addNamedProperty($propObjectName, $additionalProperty, $newAdditionalProperty);
													} else {
														//DISCARD
														//echo "<BR><STRONG>DISCARD</STRONG> $propObjectName - $propPropertyName";
														$discard = true;
													}
												} else {
													//DISCARD
													//echo "<BR><STRONG>DISCARD</STRONG> $propObjectName - $propPropertyName";
													$discard = true;
												}
											}
										}
									}
									
									//echo "<BR>ADD PROPERTY XXX = " . $propObjectName . " - " . $row["name"] . " - 0<BR>";
									if ($q->getProperty($propObjectName, $row["name"]) == null) {
										//echo "<BR>ADD PROPERTY = " . $propObjectName . " - " . $row["name"] . " - 0<BR>";
										$q->addNamedProperty($propObjectName, $row["name"], $newProperty);
									} 
								
								} else {
									//DISCARD
									//echo "<BR><STRONG>DISCARD</STRONG> $propObjectName - $propPropertyName";
									$discard = true;
								}
								
							} else {
								//DISCARD
								//echo "<BR><STRONG>DISCARD</STRONG> $propObjectName - $propPropertyName";
								$discard = true;
							}
						}
					}
				}
				
				//add conditions
				if (!$discard) {
					foreach ($conditions as $condObjectName=>$condGroup) {
						foreach ($condGroup as $condPropertyName=>$condSubgroup) {
							foreach ($condSubgroup as $condRef=>$condition) {
								
								if ($objects[$condObjectName]->getType() == 'NETWORK') {
									$tmpCondition = clone $condition;
									$q->addNamedCondition($condObjectName, $condPropertyName, $condRef, $tmpCondition);
									
								} else if ($objects[$condObjectName]->getType() == 'ONTOLOGY') {
									
									//in this case left id refers to the ontology
									$sql_query = 	"SELECT id, query
													FROM sociql_property
													WHERE ont_property = ".$condition->getLeftId()." AND actor_fk = ".$q->getObject($condObjectName)->getId();
									
									$sql_result = DB::query($sql_query, $conn);
									
									if ($row = DB::fetchAssoc($sql_result)) {
										$tmpCondition = clone $condition;
										
										$tmpCondition->setLeftId($row["id"]);
										$tmpCondition->setLeftQuery($row["query"]);
										
										$q->addNamedCondition($condObjectName, $condPropertyName, $condRef, $tmpCondition);
										//echo "<BR>ADD CONDITION = " . $condObjectName . " - " . $condPropertyName . " - 0"; 
									
									} else {
										//DISCARD
										//echo "<BR><STRONG>DISCARD</STRONG> $propObjectName - $propPropertyName";
										$discard = true;
									}
								}
							}	
						}
					}
				}
				
				if (!$discard) {
					//echo "<BR><STRONG>";
					//print_r($levels);
					//echo "</STRONG><BR>";
					$ontCounter++;
					array_push($ontQueries, $q);
					//echo "<BR>======> ADD QUERY".sizeof($ontQueries)."<BR>";
				}
			}
			
			
			$counter ++;
		}
		
		if (sizeof($ontArtifacts) > 0 && sizeof($ontQueries) == 0) {
			throw new Exception("Impossible to resolve ontological query");
		}
		
		return $ontQueries;
	}
}
?>