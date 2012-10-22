<?
/*
REST Service for the creation of an actor, property, or relation in the SociQL model
Parameters:
	-type: actor | property | relation | site
	
		-site: id, name, endpoint, maxstore, type
		-actor: id, name, realname, query, actorid
		-property: id, actorfk, name, realname, query, queriable, optimizable, tablename, sortable, significant
		-relation: id, name, property1fk, realname1, property2fk, realname2, query,	cardinality
		
	-common parameters: id, name, query.
*/

if (isset($_POST["type"]))
{
	include_once("db.inc.php");
	include_once("DB.php");

	$type = strtolower($_POST["type"]);
	$action = strtolower($_POST["action"]);
	
	switch ($type)
	{
		case "actor":
		
					if($_POST["idActor"]!="" && $_POST["nameActor"]!="" && $_POST["siteActor"]!="" && $_POST["realnameActor"]!="" && $_POST["queryActor"]!="" && $_POST["actoridActor"]!="")
					{										
						$id=$_POST["idActor"];
						$name=$_POST["nameActor"];
						$site=$_POST["siteActor"];
						$realname=$_POST["realnameActor"];
						$query=$_POST["queryActor"];
						$actorid=$_POST["actoridActor"];
						
						if($action=="edit")
						{
							$query = "UPDATE sociql_actor SET site_fk='".$site."', name='".$name."', real_name='".$realname."', query='".$query."', actor_id='".$actorid."' WHERE id=".$id;
							$result = DB::query($query, $conn);
						}
						else if($action=="create")
						{
							$query = "INSERT INTO sociql_actor (id, site_fk, name, real_name, query, actor_id, url, url_required_prop, map_x_prop, map_y_prop, ont_entity) VALUES ('".$id."', '".$site."', '".$name."', '".$realname."', '".$query."', '".$actorid."', NULL, NULL, NULL, NULL, NULL)";
							$result = DB::query($query, $conn);
						}
						
						mysql_close($con);
						echo "<executed>Maintenance Transaction -> Actor [".$id.", ".$name.", ".$realname.", ".$query.", ".$actorid."]</executed>";
					}
					else
					{
						echo "<error>You must provide valid parameters for the Actor creation</error>";
					}
					
					break;
						
		case "property":
					if($_POST["idProp"]!="" && $_POST["actorfkProp"]!="" && $_POST["nameProp"]!="" && $_POST["realnameProp"]!="" && $_POST["queryProp"]!="" && $_POST["queriableProp"]!=""&& $_POST["optimizableProp"]!=""&& $_POST["tablenameProp"]!=""&& $_POST["sortableProp"]!=""&& $_POST["significantProp"]!="")
					{
						$id=$_POST["idProp"];
						$actorfk=$_POST["actorfkProp"];
						$name=$_POST["nameProp"];
						$realname=$_POST["realnameProp"];
						$query=$_POST["queryProp"];
						$queriable=$_POST["queriableProp"];
						$optimizable=$_POST["optimizableProp"];
						$tablename=$_POST["tablenameProp"];
						$sortable=$_POST["sortableProp"];
						$significant=$_POST["significantProp"];
						
						if($action=="create")
						{
							$queryE = "INSERT INTO sociql_property (id, actor_fk, relation_fk, name, real_name, query, queriable, optimizable, table_name, type, sortable, significant, sparql, fb_disj_query, ont_property) VALUES (".$id.",".$actorfk.", 0, '".$name."', '".$realname."', '".$query."',".$queriable .", '".$optimizable."', '".$tablename."', 'nominal', '".$sortable."', '".$significant."', NULL, NULL, NULL)";
							$result = DB::query($queryE, $conn);
						}
						else if($action=="edit")
						{
							$queryE = "UPDATE sociql_property SET actor_fk=".$actorfk.", name='".$name."', real_name='".$realname."', query='".$query."', queriable=".$queriable.", optimizable ='".$optimizable."', table_name='".$tablename."', sortable='".$sortable."', significant='".$significant."'  WHERE id=".$id;
							$result = DB::query($queryE, $conn);
						}				
						
						mysql_close($con);
						echo "<executed>Maintenance Transaction -> Property [".$id.", ".$actorfk.", ".$name.", ".$realname.", ".$query.", ".$queriable.", ".$optimizable.", ".$tablename.", ".$sortable.", ".$significant."]</executed>";

					}
					else
					{
						echo "<error>You must provide valid parameters for the Property creation</error>";
					}
					break;
		case "relation":
					if($_POST["idRel"]!="" && $_POST["nameRel"]!="" && $_POST["property1fkRel"]!=""&& $_POST["realname1Rel"]!="" && $_POST["property2fkRel"]!="" && $_POST["realname2Rel"]!="" && $_POST["queryRel"]!="" && $_POST["cardinalityRel"]!="")
					{
						$id=$_POST["idRel"];
						$name=$_POST["nameRel"];
						$property1fk=$_POST["property1fkRel"];
						$realname1=$_POST["realname1Rel"];
						$property2fk=$_POST["property2fkRel"];
						$realname2=$_POST["realname2Rel"];
						$query=$_POST["queryRel"];
						$cardinality=$_POST["cardinalityRel"];
						$queryE="INSERT INTO sociql_relation (id, name, property1_fk, real_name1, property2_fk, real_name2, query, direction, fb_disj_query, cardinality, ont_relation) VALUES ('".$id."', '".$name."', ".$property1fk.", '".$realname1."', ".$property2fk.", '".$realname2."', '".$query."', 2, NULL, '".$cardinality."', NULL);";
					
						if($action=="create")
						{
							$queryE="INSERT INTO sociql_relation (id, name, property1_fk, real_name1, property2_fk, real_name2, query, direction, fb_disj_query, cardinality, ont_relation) VALUES ('".$id."', '".$name."', ".$property1fk.", '".$realname1."', ".$property2fk.", '".$realname2."', '".$query."', 2, NULL, '".$cardinality."', NULL);";
							$result = DB::query($queryE, $conn);
						}
						else if($action=="edit")
						{
							$queryE = "UPDATE sociql_relation SET name='".$name."', property1_fk=".$property1fk.", real_name1='".$realname1."', property2_fk=".$property2fk.", real_name2='".$realname2."', query='".$query."', cardinality='".$cardinality."' WHERE id=".$id;
							$result = DB::query($queryE, $conn);
						}
						mysql_close($con);
						echo "<executed>Maintenance Transaction -> Relation [".$id.", ".$name.", ".$property1fk.", ".$realname1.", ".$property2fk.", ".$realname2.", ".$query.", ".$cardinality."]</executed>";			
					}
					else
					{
						echo "<error>You must provide valid parameters for the Relation creation</error>";
					}
					break;
						
		case "site":
					if($_POST["idSite"]!="" && $_POST["nameSite"]!="" && $_POST["endpointSite"]!="")
					{
						$id=$_POST["idSite"];
						$name=$_POST["nameSite"];
						$endpoint=$_POST["endpointSite"];
						
						if($action=="create")
						{
							$query = "INSERT INTO sociql_site (id, name, endpoint, max_store, type, username, password, prefixes) VALUES (".$id.", '".$name."', '".$endpoint."', '-1', 'sql', NULL, NULL, NULL);";
							$result = DB::query($query, $conn);
						}
						else if($action=="edit")
						{
							$query= "UPDATE sociql_site SET id=".$id.", name='".$name."', endpoint='".$endpoint."'WHERE id=".$id;
							$result = DB::query($query, $conn);
						}
						
						mysql_close($con);
						echo "<executed>Maintenance Transaction -> Site [".$id.", ".$name.", ".$endpoint."]</executed>";
					}
					else
					{
						echo "<error>You must provide valid parameters for the Site creation</error>";
					}
					break;
	}
}
else
{
	echo "<error>Type is obligatory</error>";
}




?>


