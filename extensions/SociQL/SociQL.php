<?php
require "Lexer.php";
require "DB.php";

class SociQL {

    private static $sociqlObject = null;
	private static $toMap = array();
	private static $toMapPlot = array();
	private static $mapCounter =0;

    /**
     * Get the test queries
     * @param string $formName Name of the html form
     * @param string $textboxName Name of the html textbox
     * @return string Html text to add on the console
     */
    public static function getTestQueriesInHtml($formName, $textboxName) {

        $variables = '
			<script>
			var query1 = "SELECT o1.name, r1.name \nFROM organization o1, researcher r1 \nWHERE affiliated(r1,o1) AND r1.name=\"Eleni Stroulia\"";
			var query2 = "SELECT u1.name, a1.name \nFROM album a1, user u1 \nWHERE userAlbum(u1,a1) AND u1.name=\"Filipe Mesquita\"";
			var query3 = "SELECT p1.title, p1.year \nFROM paper p1, researcher r1 \nWHERE writes(r1,p1) AND r1.name=\"Eleni Stroulia\"";
			var query4 = "SELECT p1.title, p1.year, r1.name \nFROM paper p1, researcher r1, paper p2 \nWHERE writes(r1,p1) AND p2.title=\"Run-Time Selection of Coordination Mechanisms in Multi-Agent Systems.\" AND cites(p1,p2)";
			var query5 = "SELECT p1.title, p1.year, v1.name \nFROM paper p1, venue v1, researcher r1 \nWHERE presented(p1,v1) AND r1.name=\"Eleni Stroulia\" AND writes(r1,p1)";
			var query6 = "SELECT r1.name, p1.title, p1.year \nFROM paper p1, researcher r1, researcher r2 \nWHERE writes(r1,p1) AND writes(r2,p1) AND r2.name=\"Eleni Stroulia\"";
			var query7 = "SELECT o1.name, r1.name, u1.birthday \nFROM organization o1, researcher r1, user u1 \nWHERE affiliated(r1,o1) AND o1.name=\"University of Alberta\" AND sameAsFB(r1, u1)";
			var query8 = "SELECT p1.title, p1.year, r1.name \nFROM paper p1, researcher r1, paper p2 \nWHERE writes(r1,p1) AND p2.title=\"Run-Time Selection of Coordination Mechanisms in Multi-Agent Systems.\" AND cites(p1,p2) AND p1.year=\"2002\"";
			var query9 = "SELECT o1.name, r1.name \nFROM organization o1, researcher r1 \nWHERE affiliated.since(r1,o1,y) AND y<1998 AND r1.name=\"Eleni Stroulia\"";
			var query10 = "MAP o1: name, url \nFROM organization o1, researcher r1 \nWHERE affiliated(r1,o1) AND r1.name=\"Eleni Stroulia\"";
			var query11 = "SELECT o1.name \nFROM organization o1 \nWHERE o1.name ><\"National\"";
			var query12 = "SELECT s1.label, s1.abstract \nFROM scientist s1 WHERE s1.label >< \"Abraham\"";
			var query13 = "SELECT s1.label, s1.abstract, r1.name, r1.url \nFROM scientist s1, researcher r1 \nWHERE sameAsDBpedia(r1, s1) AND s1.label >< \"Abraham\"";
			var query14 = "SELECT s1.label, s1.abstract, r1.name, r1.url \nFROM scientist s1, researcher r1 \nWHERE sameAsDBpedia(r1, s1) AND r1.name >< \"Michael\"";
			var query15 = "SELECT o2.name, r2.name \nFROM organization o1, researcher r1, organization o2, researcher r2 \nWHERE affiliated(r1,o1) AND r1.name=\"Eleni Stroulia\" AND affiliated(r2,o2) AND o1.name=o2.name";
			var query16 = "SELECT c1.name, c1.province, c2.label, c2.leaderName, l1.label \nFROM City c1, cityDB c2, Leader l1 \nWHERE sameAsDBpediaCity(c1,c2) AND cityLeader(c2,l1) AND c1.name=\"Edmonton\"";
			var query17 = "SELECT o1.name, r1.name, u1.birthday, e1.name \nFROM Organization o1, Researcher r1, User u1, Event e1 \nWHERE Affiliated(r1,o1) AND o1.name=\"University of Alberta\" AND SameAsFB(r1, u1) AND EventMember(u1,e1)";
			var query18 = "SELECT c2.label, c2.leaderName, l1.label \nFROM cityDB c2, Leader l1 \nWHERE cityLeader(c2,l1) AND c2.label=\"Edmonton\"";
			var query21 = "SELECT o1.name, r1.name \nFROM organization o1, researcher r1 \nWHERE UNDEF(r1,o1,4) AND r1.name=\"Eleni Stroulia\"";
			var query22 = "SELECT i1.name, p1.name \nFROM ONT.INDIVIDUAL i1, ONT.PRODUCT p1 \nWHERE ONT.COLLABORATES(i1,p1) AND i1.name=\"Eleni Stroulia\"";
			var query23 = "SELECT p1.title, p1.year, r1.name \nFROM paper p1, researcher r1 \nWHERE writes(r1,p1) AND r1.name><\"Jan\" \nORDER BY DEGREE r1";
                        </script>';

        $links = '
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query1">Example 1</a>: Simple query (Reason) <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query2">Example 2</a>: Simple query (Facebook) <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query3">Example 3</a>: Papers of a researcher <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query4">Example 4</a>: Citations to a paper <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query5">Example 5</a>: Paper and associated venue <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query6">Example 6</a>: Coauthors of a researcher<br> 
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query7">Example 7</a>: Name(Reason) and Birthday(FB) - merging external data <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query8">Example 8</a>: Citations to a article only in 2002 <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query9">Example 9</a>: Affiliations of Eleni before 1998 <br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query10">Example 10</a>: Map query<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query11">Example 11</a>: Organizations that contain \'National\'<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query12">Example 12</a>: Simple query to DBpedia (remote)<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query13">Example 13</a>: Abstract(DBpedia) - Url(Reason) - merging external data<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query14">Example 14</a>: Same as #13 but with condition on Reason side<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query15">Example 15</a>: Variable as condition<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query16">Example 16</a>: DBpedia and Reason optimized<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query17">Example 17</a>: Facebook and Reason optimized<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query18">Example 18</a>: Optimization without \'Reason\'<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query21">Example 21</a>: UNDEF relationship<br>
			<a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query22">Example 22</a>: Ontologies<br>
                        <a href="#" onClick="javascript: document.' . $formName . '.' . $textboxName . '.value = query23">Example 23</a>: ORDER BY<br>';

        return $variables . "\n" . $links;
    }


    /**
     * Set database dialect
     * @param string $dialect Database dialect
     */
    public static function setDialect($dialect) {
        DB::setDialect($dialect);
    }


    /**
     * Parse a SociQL query
     * @param string $query SociQL query
     * @return string SQL query
     */
    public static function parseQuery($query) {
        $lexer = new Lexer($query);

        try {
            self::$sociqlObject = new ParserSociQL($query);
            return $parsedSQL = self::$sociqlObject->parse();

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * Get projected attributes from the parsed query
     * @return array List of projected attributes
     */
    public static function getProjectedAttributes() {

        if (self::$sociqlObject != null) {
            return self::$sociqlObject->getProjectionAttributes();
        }

        return null;
    }


    /**
     * Get the parser object
     * @return ParserSociQL Parser
     */
    public static function getParser() {
        return self::$sociqlObject;
    }


    public static function getModelScript() {

        $yui = '<style type="text/css">
                <!--
                .Estilo2 {font-size: x-small}
                .Estilo3 {font-size: small}
                -->
                </style>
                <style type="text/css">
                /*margin and padding on body element
                  can introduce errors in determining
                  element position and are not recommended;
                  we turn them off as a foundation for YUI
                  CSS treatments. */
                body {
                        margin:0;
                        padding:0;
                }
                </style>

                <style type="text/css">
                .icon-actor { display:block; height: 22px; padding-left: 20px; background: transparent url(http://hypatia.cs.ualberta.ca/reason/wiki2/extensions/SRN/img/ico_actor.png) 0 0px no-repeat; }
                .icon-site { display:block; height: 22px; padding-left: 20px; background: transparent url(http://hypatia.cs.ualberta.ca/reason/wiki2/extensions/SRN/img/ico_site.png) 0 0px no-repeat; }
                .icon-prop { display:block; height: 22px; padding-left: 20px; background: transparent url(http://hypatia.cs.ualberta.ca/reason/wiki2/extensions/SRN/img/ico_property.png) 0 4px no-repeat; }
                .icon-req { display:block; height: 22px; padding-left: 20px; background: transparent url(http://hypatia.cs.ualberta.ca/reason/wiki2/extensions/SRN/img/ico_required.png) 0 0px no-repeat; }
                .icon-rel { display:block; height: 22px; padding-left: 20px; background: transparent url(http://hypatia.cs.ualberta.ca/reason/wiki2/extensions/SRN/img/ico_relation.png) 0 2px no-repeat; }
                .htmlnodelabel { margin-left: 20px; }
                </style>

                <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/treeview/assets/skins/sam/treeview.css" />
                <script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
                <script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/treeview/treeview-min.js"></script>


                <!--begin custom header content for this example-->
                <!--bring in the folder-style CSS for the TreeView Control-->
                <link rel="stylesheet" type="text/css" href="../../build/treeview/assets/treeview-menu.css" />

                <!-- Some custom style for the expand/contract section-->
                <style>
                #expandcontractdiv {border:1px dotted #dedede; background-color:#EBE4F2; margin:0 0 .5em 0; padding:0.4em;}
                #treeDiv1 { background: #fff; }
                </style>';

        return $yui;
    }


    public static function getModelTree() {

        include "db.inc.php";

        $tree = '<!-- markup for expand/contract links -->
                <div style="background-color:#EEEEEE;"><strong>Model</strong></div>
                <div id="treeDiv1" class="Estilo3"></div>

                <script type="text/javascript">
                //an anonymous function wraps our code to keep our variables
                //in function scope rather than in the global namespace:
                (function() {
                        var tree; //will hold our TreeView instance

                        function treeInit() {

                                YAHOO.log("Example\'s treeInit function firing.", "info", "example");

                                //Hand off ot a method that randomly generates tree nodes:
                                buildTextNodeTree();

                                //handler for collapsing all nodes
                                YAHOO.util.Event.on("collapse", "click", function(e) {
                                        YAHOO.log("Collapsing all TreeView  nodes.", "info", "example");
                                        tree.collapseAll();
                                        YAHOO.util.Event.preventDefault(e);
                                });
                        }

                        //This method will build a TreeView instance and populate it
                        function buildTextNodeTree() {

                                //instantiate the tree:
                                tree = new YAHOO.widget.TreeView("treeDiv1");

                                ';



                        $query = "SELECT id, name
                                FROM sociql_site
                                ORDER BY name";
                        $result_site = DB::query($query, $conn);

                        while ($row_site = DB::fetchArray($result_site))
                        {
                                $tree .= 'var tmpNodeSite = new YAHOO.widget.TextNode("'.$row_site["name"].'", tree.getRoot(), false);';

                                $site_id = $row_site["id"];

                                //Actors
                                $query = "SELECT id, name
                                        FROM sociql_actor
                                        WHERE site_fk = $site_id
                                        ORDER BY name";
                                $result = DB::query($query, $conn);

                                while ($row = DB::fetchArray($result))
                                {
                                        $tree .= 'var tmpNodeActor = new YAHOO.widget.TextNode("'. $row["name"].'", tmpNodeSite, false);';

                                        //Properties
                                        $query = "SELECT id, name
                                                FROM sociql_property
                                                WHERE actor_fk = ".$row["id"]."
                                                ORDER BY name";
                                        $result_prop = DB::query($query, $conn);

                                        while ($row_prop = DB::fetchArray($result_prop))
                                        {
                                                $tree .= 'var tmpNodeProp = new YAHOO.widget.TextNode("'.$row_prop["name"].'", tmpNodeActor, false);
                                                tmpNodeProp.labelStyle = "icon-prop";';
                                        }


                                        //Required sets
                                        $query = "SELECT id, name
                                                FROM sociql_requiredset
                                                WHERE actor_fk = ".$row["id"]."
                                                ORDER BY name";
                                        $result_req = DB::query($query, $conn);


                                        while ($row_req = DB::fetchArray($result_req))
                                        {
                                                $tree .= 'var tmpNodeReq = new YAHOO.widget.TextNode("'. $row_req["name"].'", tmpNodeActor, false);
                                                tmpNodeReq.labelStyle = "icon-req";';
                                        }




                                        //Relations
                                        $query = "SELECT id, name, property1_fk, property2_fk
                                                FROM sociql_relation
                                                WHERE property1_fk IN
                                                        (SELECT id
                                                        FROM sociql_property
                                                        WHERE actor_fk = ".$row["id"].") OR
                                                      property2_fk IN
                                                        (SELECT id
                                                        FROM sociql_property
                                                        WHERE actor_fk = ".$row["id"].") ORDER BY name";
                                        $result_rel = DB::query($query, $conn);


                                        while ($row_rel = DB::fetchArray($result_rel))
                                        {
                                                $tree .= 'var tmpNodeRel = new YAHOO.widget.TextNode("'. $row_rel["name"].'", tmpNodeActor, false);
                                                tmpNodeRel.labelStyle = "icon-rel";';

                                                $query = "SELECT sociql_site.name AS site, sociql_actor.name AS actor, sociql_property.name AS prop
                                                        FROM sociql_property, sociql_actor, sociql_site
                                                        WHERE (sociql_property.id = ".$row_rel["property1_fk"]." OR
                                                            sociql_property.id = ".$row_rel["property2_fk"].") AND
                                                            actor_fk = sociql_actor.id AND site_fk = sociql_site.id";
                                                $result_proprel = DB::query($query, $conn);

                                                while ($row_proprel = DB::fetchArray($result_proprel))
                                                {
                                                        $name = $row_proprel["actor"].".".$row_proprel["prop"];
                                                        if ($row_proprel["site"] != $row_site["name"])
                                                        {
                                                                $name = $row_proprel["site"].".".$name;
                                                        }

                                                        $tree .= 'var tmpNodePropRel = new YAHOO.widget.TextNode("'. $name.'", tmpNodeRel, true);
                                                        tmpNodePropRel.labelStyle = "icon-prop";';

                                                }
                                        }

                                }
                        }

                        $tree .= '//once it\'s all built out, we need to render
                        //our TreeView instance:
                        tree.draw();
                }


                //When the DOM is done loading, we can initialize our TreeView
                //instance:
                YAHOO.util.Event.onDOMReady(treeInit);

                })();//anonymous function wrapper closed; () notation executes function

                </script>';
                
        DB::close($conn);
        
        return $tree;
    }


    public static function getFacebookInitialization() {
        require 'facebook/facebook.php';

        // Create our Application instance.
        $facebook = new Facebook(array(
            'appId' => '37271985873',
            'secret' => 'b4d430d0bc25d8029ada6501b72b3503',
            'cookie' => true,
        ));

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
        
        // login or logout url will be needed depending on current user state.
        if ($me) {
            $logoutUrl = $facebook->getLogoutUrl();
        } else {
            $loginUrl = $facebook->getLoginUrl();
        }
    }
    
	public function getMap()
	{
		$map = "";
		$map .= "<script type=\"text/javascript\">
				  function initialize() {
					var latlng = new google.maps.LatLng(33.870416, -70.488281);
					var myOptions = {
					  zoom: 3,
					  center: latlng,
					  mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					var map = new google.maps.Map(document.getElementById(\"map_canvas\"), myOptions);\n";
					
					for ($i = 0; $i < count(self::$toMapPlot); $i++) {
						$map.= "var myLatlng".$i." = new google.maps.LatLng(".self::$toMapPlot[$i].");\n";		
					}
					
					for ($i = 0; $i < count(self::$toMap); $i++) {
						$map.= "var marker".$i." = new google.maps.Marker({position: myLatlng".$i.", title:\"".self::$toMap[$i]."\"});\n";
						$map.= "marker".$i.".setMap(map);\n\n";
					}
		$map .= "} 

					$(document).ready(function(){
						$('#map_button').click(function(){
							if($('#map_canvas').css('display') == 'none'){
								$('#map_canvas').slideDown('fast', function(){
									initialize();
									$('html, body').animate({scrollTop: $(document).height()}, 1000);
								});
								$('#map_button').html('Hide Map Results');
							}
							else{
								$('#map_canvas').slideUp('fast');
								$('#map_button').html('Show Map Results');
							}
						});
					});
				  
				</script>";
		return $map;
	}
    
    public static function getResult($query, $format = 'HTML') {

        $table = "";
        
        if (isset($query)) {

            if ($format == "XML") {
                $table .= "\n<Graph>";
            }

            try {
                $sqlQuery = SociQL::parseQuery($query);

                $parser = SociQL::getParser();
                $objects = $parser->getQueryObjects();
                $properties = $parser->getQueryProperties();

                include "db.inc.php";

                $projectedAttributes = SociQL::getProjectedAttributes();

                $sql_result = DB::query($sqlQuery, $conn);
                
                if (DB::numRows($sql_result)) {

                    $rowLinks = array();

                    if ($format == "XML") {

                        $relations = $parser->getQueryRelations();

                        foreach ($relations as $relationName=>$relGroup) {
                            foreach ($relGroup as $relationPropName=>$relSubgroup) {
                                foreach ($relSubgroup as $relationRef=>$relation) {
                                    $actorName1 = $relation->getActorName1();
                                    $actorName2 = $relation->getActorName2();

                                    if (isset($rowLinks[$actorName1])) {
                                        if (!in_array($actorName2, $rowLinks[$actorName1])) {
                                            array_push($rowLinks[$actorName1], $actorName2);
                                        }
                                    } else {
                                        $rowLinks[$actorName1] = array($actorName2);
                                    }

                                }
                            }
                        }
                    }


                    $orderAlgorithm = SociQL::getParser()->getQueryOrderAlgorithm();
                    $orderObject = SociQL::getParser()->getQueryOrderObject();
                    $createdNodes = array();
                    $results = array();
					$attrb = array();

                    if ($orderAlgorithm != null && $orderObject != null) {
                        include_once "scoreParser.php";
                        $scoreParser = new scoreParser($orderObject, SociQL::getParser()->queryToString(), $orderAlgorithm);

                        $projAttribute = new ProjectionAttribute();
                        $projAttribute->setValue("score");
                        $projAttribute->setVisibility(true);
                        $projAttribute->setIsId(false);
                        $projAttribute->setObjectName($orderObject);
                        $projAttribute->setPropertyName('score');
                        $projAttribute->setPreferredName('score');
                        array_push($projectedAttributes, $projAttribute);
                    }

                    //Table headers
                    if ($format != "XML") {

                        //echo "<BR><i>$sqlQuery</i><BR>";
                        if ($format == "WIKI") {
                            $table .= "{| class='wikitable sortable' border='1'\n|+Query Results\n|-\n";
                        } else if ($format == "HTML") {
                            $table .= "\n<table border='1'>";
                        }

                        //Table headers
                        if ($format == "HTML") {
                            $table .= "\n<tr>";
                        }
                        
                        $isFirst = true;
						
					
                        foreach ($projectedAttributes as $keyName=>$projectionAttr) {
                            if ($projectionAttr->isVisible()) {
                                if (!$isFirst && $format == "WIKI") {
                                    $table .= "!";
                                }

								$found = 0;
                                if ($format == "WIKI") {
								// This is the names of the columns in the result table
                                    $table .= "!" . $projectionAttr->getPreferredName();
									$column = $projectionAttr->getPreferredName();
									array_push($attrb, $column);
									//echo $column;
									if($column == "username")
									{
										$found = 1;			
										//echo self::$mapCounter;
									}
									
									if($found == 0)
									{
										self::$mapCounter += 1;
									}
									
                                } else if ($format == "HTML") {
									$column = $projectionAttr->getPreferredName();
                                    $table .= "\n<td><strong>" . $column . "</strong></td>";
									
										
                                }

                                $isFirst = false;
                            }
                        }
						
						
                        if ($format == "HTML") {
                            $table .= "\n</tr>";
                        }
                    }
					
					
                    while ($row = DB::fetchRow($sql_result)) {

                        if ($orderAlgorithm != null && $orderObject != null) {
                            $counter = 0;
                            foreach ($row as $key=>$val) {
                                if ($projectedAttributes[$counter]->isId() && $projectedAttributes[$counter]->getObjectName() == $orderObject) {
                                    $row['score'] = $scoreParser->getScore($val);
                                }
                                $counter++;
								
                            }
                        }
						
                        array_push($results, $row);
						

                    }
					//SociQL::createTransactionalTable($attrb, $results);
					
                    //sort by score
                    if ($orderAlgorithm != null && $orderObject != null) {
                        usort($results, 'compareScore');
                    }

                    foreach ($results as $row) {
                        if ($format == "WIKI") {
                            $table .= "\n|-\n";
                        } else if ($format == "HTML") {
                            $table .= "\n<tr>";
                        }

                        $rowIds = array();
                        $counter = 0;
                        $isFirst = true;
						$mapAble =0;
                        foreach ($row as $key=>$val) {
                            if ($projectedAttributes[$counter]->isVisible()) {

                                if ($projectedAttributes[$counter]->isSignificant()) {
                                    $objectName = $projectedAttributes[$counter]->getObjectName();

                                    $url = $objects[$objectName]->getBaseUrl();

                                    //if it has some additional properties for the url
                                    if (sizeof($objects[$objectName]->getRequiredProps()) > 0) {

                                            //look for all the required props for url
                                            for ($j=0; $j<sizeof($objects[$objectName]->getRequiredProps()); $j++) {

                                                    $propertyName = trim($objects[$objectName]->getRequiredProp($j));

                                                    if ($propertyName != "") {

                                                        $propertyRealName = $propertyName;

                                                        if ($properties[$objectName][$propertyName]->getRealName() != "") {
                                                            $propertyRealName = $properties[$objectName][$propertyName]->getRealName();
                                                        }

                                                        $key = null;

                                                        foreach ($projectedAttributes as $keyName=>$projectionAttr) {
                                                            if ($projectionAttr->getObjectName() == $objectName &&
                                                                $projectionAttr->getPropertyName() == $propertyName) {
                                                                $key = $keyName;
                                                            }
                                                        }


                                                        if ($key !== null) {
                                                                //if it is from reason, then change spaces by _
                                                                if ($objects[$objectName]->getSiteId() == 1) {
                                                                    $url = str_replace("<$propertyName>", urlencode(str_replace(" ", "_", $row[$key])), $url);
                                                                } else {
                                                                    $url = str_replace("<$propertyName>", urlencode($row[$key]), $url);
                                                                }
                                                        }
                                                    }
                                            }
                                    }
                                }

                                if ($format != "XML") {
                                    if (!$isFirst && $format == "WIKI") {
                                        $table .= "|";
                                    }
                                    
                                    if ($format == "WIKI") {
                                        $table .= "| ";
                                    } else if ($format == "HTML") {
                                        $table .= "<td>";
                                    }

                                    if ($projectedAttributes[$counter]->isSignificant()) {
                                        if ($format == "WIKI") {
                                            $table .= "[$url ";
                                        } else if ($format == "HTML") {
                                            $table .= "\n<a href='$url' target='_blank'>";
                                        }
                                    }
									
																
									if($projectedAttributes[$counter]->getPropertyName() == "username")
									{
										array_push(self::$toMap, $val);
									}
									
									if($projectedAttributes[$counter]->getPropertyName() != "tomap")
									{
										$table .= str_replace(array("\n","\r"), array(" "," "), $val);
									}
									if($projectedAttributes[$counter]->getPropertyName() == "tomap")
									{
										$table .= "below";
										array_push(self::$toMapPlot, $val);
									}
									
                                    
									

                                    if ($projectedAttributes[$counter]->isSignificant()) {
                                        if ($format == "WIKI") {
                                            $table .= "]";
                                        } else if ($format == "HTML") {
                                            $table .= "</a>";
                                        }
                                    }

                                    if ($format == "HTML") {
                                        $table .= "</td>";
                                    }

                                    $isFirst = false;
                                }
                            }

                            if ($format == "XML") {
                                //if (isset($_GET["options"]) && $_GET["options"] == "onlyId") {
                                    if ($projectedAttributes[$counter]->isId()) {
                                        $rowIds[$projectedAttributes[$counter]->getObjectName()] = $val;

                                        if (isset($createdNodes[$projectedAttributes[$counter]->getObjectName()])) {
                                            if (!in_array($val, $createdNodes[$projectedAttributes[$counter]->getObjectName()])) {
                                                array_push($createdNodes[$projectedAttributes[$counter]->getObjectName()], $val);
                                            }
                                        } else {
                                            $createdNodes[$projectedAttributes[$counter]->getObjectName()] = array($val);
                                        }
                                    }
                                //}
                            }
							$mapAble ++;
                            $counter ++;
                        }
						
                        if ($format == "HTML") {
                            $table .= "\n</tr>";
                        }

                        if ($format == "XML") {

                            foreach ($rowIds as $objectName=>$objectId) {

                                if (isset($rowLinks[$objectName])) {
                                    for ($i=0; $i<sizeof($rowLinks[$objectName]); $i++) {
                                        $table .= "<Edge fromId=\"$objectId\" fromType=\"$objectName\" toId=\"".$rowIds[$rowLinks[$objectName][$i]]."\" toType=\"".$rowLinks[$objectName][$i]."\" label=\"\" type=\"BI\" />\n";
                                    }
                                }
                            }
                        }
                    }
					//print_r(self::$toMap);

                    if ($format == "XML") {

                        foreach ($createdNodes as $objectName=>$objectIds) {
                            foreach ($objectIds as $idIndex=>$objectId) {
                                $table .= "<Node id=\"$objectId\" type=\"$objectName\"></Node>\n";
                            }
                        }
                    }

                } else {
                    if ($format != "XML") {
                        $table .= "\n<tr><td>No results</td></tr>";
                    }
                }

                if ($format == "WIKI") {
                    $table .= "\n|}";
                } else if ($format == "HTML") {
                    $table .= "\n</table>";
                }

                if ($format == "XML") {
                    $table .= "\n</Graph>";
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        //print_r(self::$toMap);
        return $table;
    }
	
	public static function createTransactionalTable($attrb, $results){
		
		include "db.inc.php";
		
		$masterIndex ="SELECT id, query_id FROM sociql_queries ORDER BY id DESC";
		$result_site = DB::query($masterIndex, $conn);
		$row_site = DB::fetchArray($result_site);
		$lastQueryIndex = $row_site["id"]+1;
		$queryID = "query".$lastQueryIndex;
	
		$createTable = "CREATE TABLE ".$queryID." (";
		$counter = 0;
		$size = count($attrb);
		foreach($attrb as $column){
			
			if($counter == $size){
				$createTable .= $column. " char(50))";
			}
			else{
				$createTable .= $column. " char(50), ";
			}
			$counter ++;
		}
		$counter = 0;
		echo $createTable.";\n";
		$result2 = DB::query($createTable, $conn);
		
		foreach($results as $row){
		
			$insertValues = "INSERT INTO ".$queryID." (";
			foreach($attrb as $column){
			
				if($counter == $size){
					$insertValues .= $column. ")";
				}
				else{
					$insertValues .= $column. ", ";
				}
				$counter ++;
			}
			$insertValues .= " VALUES (";
			$counter = 0;
				
			foreach($row as $value){
				
				if($counter != 0){
					if($counter == $size+1){
						$insertValues .= "'".$value."')";
					}
					else{
						$insertValues .= "'".$value."', ";
					}
					$counter ++;
				}
				else{
					$counter ++;
				}
			}
			$counter = 0;
			echo $insertValues;
			$result = DB::query($insertValues, $conn);
		}
	
		$updateMasterQuery = "INSERT INTO sociql_queries (query_id) VALUES ('master')";
		$result3 = DB::query($updateMasterQuery, $conn);
	}
}



?>