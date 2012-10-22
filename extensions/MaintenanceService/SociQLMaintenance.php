<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SociQLMaintenance'] = 'SociQLMaintenance'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SociQLMaintenance'] = $dir . 'SociQLMaintenance.i18n.php';
$wgSpecialPageGroups['SociQLMaintenance'] = 'sociql-tools';

function runSociQLMaintenance($par) {
  SociQLMaintenance::run($par);
}

class SociQLMaintenance extends SpecialPage{

    function SociQLMaintenance() {
		wfLoadExtensionMessages('SociQLMaintenance');
		SpecialPage::SpecialPage("SociQLMaintenance", '', true, 'runSociQLMaintenance');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$user = Person::newFromId($wgUser->getId());
		if($wgUser->isLoggedIn() && ($user->isRole(MANAGER) ||$user->isRole(STAFF))){
		    if(isset($_POST['submitButton'])){
			    self::processRequest();
			    $wgOut->addHTML("<hr />");
			}
			self::outputForm();
		}
		else {
			$wgOut->addHTML("You must be part of the ".STAFF." or ".MANAGER." role to view this page.");
		}
	}
	
	function processRequest(){
	    global $wgOut, $con;
	    if (isset($_POST["type"])){
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
						
						        mysql_close($conn);
						        $wgOut->addHTML("<executed>Maintenance Transaction -> Actor [".$id.", ".$name.", ".$realname.", ".$query.", ".$actorid."]</executed>");
					        }
					        else
					        {
						        $wgOut->addHTML("<error>You must provide valid parameters for the Actor creation</error>");
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
						        $wgOut->addHTML("<executed>Maintenance Transaction -> Property [".$id.", ".$actorfk.", ".$name.", ".$realname.", ".$query.", ".$queriable.", ".$optimizable.", ".$tablename.", ".$sortable.", ".$significant."]</executed>");

					        }
					        else
					        {
						        $wgOut->addHTML("<error>You must provide valid parameters for the Property creation</error>");
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
						        $wgOut->addHTML("<executed>Maintenance Transaction -> Relation [".$id.", ".$name.", ".$property1fk.", ".$realname1.", ".$property2fk.", ".$realname2.", ".$query.", ".$cardinality."]</executed>");			
					        }
					        else
					        {
						        $wgOut->addHTML("<error>You must provide valid parameters for the Relation creation</error>");
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
						        $wgOut->addHTML("<executed>Maintenance Transaction -> Site [".$id.", ".$name.", ".$endpoint."]</executed>");
					        }
					        else
					        {
						        $wgOut->addHTML("<error>You must provide valid parameters for the Site creation</error>");
					        }
					        break;
	        }
        }
        else
        {
	        $wgOut->addHTML("<error>Type is obligatory</error>");
        }
	}

    function outputForm(){
        global $wgOut, $wgScriptPath, $wgServer;
        $wgOut->addExtensionStyle("$wgServer$wgScriptPath/extensions/MaintenanceService/st.css");
        $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:SociQLMaintenance' method='post'>
				    <label><b>Type:</b></label> <input type='radio' name='type' value='actor' checked/> Actor				
				    <input type='radio' name='type' value='property'/> Property
				    <input type='radio' name='type' value='relation'/> Relation 
				    <input type='radio' name='type' value='site'/> Site <br />
				    <label><b>Action:</b></label> <input type='radio' name='action' value='create' checked/> Create
				    <input type='radio' name='action' value='edit'/> Edit<br />
		    <hr />
				    <b>Required for Actor Creation: </b><br/>
				    <label>ID:</label> <input type='text' name='idActor' /><br />
				    <label>Name:</label> <input type='text' name='nameActor' /><br />
				    <label>Site FK:</label> <input type='text' name='siteActor' /><br />
				    <label>Real Name:</label> <input type='text' name='realnameActor' /><br />
				    <label>Query:</label> <input type='text' name='queryActor' /><br />
				    <label>Actor ID:</label> <input type='text' name='actoridActor' /><br />
		    <hr />

				    <b>Required for Property Creation: </b><br/>
				    <label>ID:</label> <input type='text' name='idProp' /><br />
				    <label>Actor FK:</label> <input type='text' name='actorfkProp' /><br />
				    <label>Name:</label> <input type='text' name='nameProp' /><br />
				    <label>Real Name:</label> <input type='text' name='realnameProp' /><br />
				    <label>Query:</label> <input type='text' name='queryProp' /><br />
				    <label>Table Name:</label> <input type='text' name='tablenameProp' /><br />
				    <label>Queriable:</label> <input type='radio' name='queriableProp' value='1' checked/> Y <input type='radio' name='queriableProp' value='0'/> N<br />
				    <label>Optimizable:</label> <input type='radio' name='optimizableProp' value='1' checked/> Y <input type='radio' name='optimizableProp' value='0'/> N<br />
				    <label>Sorteable:</label> <input type='radio' name='sortableProp' value='1' checked/> Y <input type='radio' name='sortableProp' value='0'/> N<br />
				    <label>Significant:</label> <input type='radio' name='significantProp' value='1' checked/> Y <input type='radio' name='significantProp' value='0'/> N<br />
		    <hr />
				    <b>Required for Relation Creation: </b><br/>
				    <label>ID:</label> <input type='text' name='idRel' /><br />
				    <label>Name:</label> <input type='text' name='nameRel' /><br />
				    <label>Property 1 FK:</label> <input type='text' name='property1fkRel' /><br />
				    <label>Property 1 Name</label> <input type='text' name='realname1Rel' /><br />
				    <label>Property 2 FK:</label> <input type='text' name='property2fkRel' /><br />
				    <label>Property 2 Name</label> <input type='text' name='realname2Rel' /><br />
				    <label>Query:</label> <input type='text' name='queryRel' /><br />
				    <label>Cardinality:</label> <input type='radio' name='cardinalityRel' value='1-N' checked/> (1-N) <input type='radio' name='cardinalityRel' value='N-N'/> (N-N)<br />
		    <hr />
				    <b>Required for Site Creation: </b><br/>
				    <label>ID:</label> <input type='text' name='idSite' /><br />
				    <label>Name:</label> <input type='text' name='nameSite' /><br />
				    <label>Endpoint:</label> <input type='text' name='endpointSite' /><br />
		    <hr />
		    <input type='submit' name='submitButton' id='submitButton' value='Send Details' />
			<input type='reset' name='resetButton' id='resetButton' value='Reset Form' style='margin-right: 20px;' />");
    }
}

?>
