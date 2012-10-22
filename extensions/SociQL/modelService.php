<?php
echo "<model>";

if (isset($_GET["type"]))
{
	include_once("db.inc.php");
	include_once("DB.php");
	DB::setDialect("MySql");

	$type = strtolower($_GET["type"]);
	//print_r($type);
	
	switch ($type)
	{
		case "actor":
						$query = "SELECT sociql_actor.id, sociql_actor.name, sociql_site.name AS site_name FROM sociql_actor, sociql_site WHERE site_fk=sociql_site.id AND site_fk=1 ORDER BY site_fk, name";
						$result = mysql_query($query, $conn);
						
						while ($row = mysql_fetch_assoc($result))
						{
							echo "\n<actor>";
							echo "\n\t<id>".$row["id"]."</id>";
							echo "\n\t<name>".$row["name"]."</name>";
							echo "\n\t<site>".$row["site_name"]."</site>";
							echo "\n</actor>";
						}
						
						break;
						
		case "relation":
						$query = "";
						if (isset($_GET["actor"]))
						{
							$query = "SELECT id, name, property1_fk, property2_fk FROM sociql_relation WHERE property1_fk IN (SELECT id FROM sociql_property WHERE actor_fk=".$_GET["actor"].") OR property2_fk IN (SELECT id FROM sociql_property WHERE actor_fk=".$_GET["actor"].") ORDER BY name";
						}
						else
						{
							$query = "SELECT id, name, property1_fk, property2_fk FROM sociql_relation ORDER BY name";
						}
						
						$result = mysql_query($query, $conn);
						
						while ($row = mysql_fetch_assoc($result))
						{
							$actor1 = $actor2 = 0;
							
							//get actor id from properties
							for ($i=1; $i<=2; $i++)
							{ 
								$str_prop = "property".$i."_fk";
								$var_actor = "actor".$i;
								
								$query = "SELECT id, name FROM sociql_actor WHERE id = (SELECT actor_fk FROM sociql_property WHERE id = ".$row[$str_prop].")";
								$result2 = mysql_query($query, $conn);
								
								if ($row2 = mysql_fetch_assoc($result2))
								{
									$$var_actor = $row2["id"];
								}
							}
														
							echo "\n<relation>";
							echo "\n\t<id>".$row["id"]."</id>";
							echo "\n\t<type>relation</type>";
							echo "\n\t<name>".$row["name"]."</name>";
							echo "\n\t<prop1>".$row["property1_fk"]."</prop1>";
							echo "\n\t<actor1>".$actor1."</actor1>";
							echo "\n\t<prop2>".$row["property2_fk"]."</prop2>";
							echo "\n\t<actor2>".$actor2."</actor2>";
							echo "\n</relation>";
							
							$query = "SELECT id, name, type FROM sociql_property WHERE relation_fk = ".$row["id"]." ORDER BY name";
							$result2 = mysql_query($query, $conn);
							
							while ($row2 =  mysql_fetch_assoc($result2))
							{
								echo "\n<relation>";
								echo "\n\t<id>".$row["id"]."</id>";
								echo "\n\t<type>relation-prop</type>";
								echo "\n\t<name>".$row["name"]."</name>";
								echo "\n\t<prop1>".$row["property1_fk"]."</prop1>";
								echo "\n\t<actor1>".$actor1."</actor1>";
								echo "\n\t<prop2>".$row["property2_fk"]."</prop2>";
								echo "\n\t<actor2>".$actor2."</actor2>";
								echo "\n\t<varname>".$row2["name"]."</varname>";
								echo "\n\t<vartype>".$row2["type"]."</vartype>";
								echo "\n</relation>";								
							}
						}
						
						break;
		
		case "property":
						if (isset($_GET["actor"]))
						{
							$actor = $_GET["actor"];
							$query = "SELECT id, name, type FROM sociql_property WHERE actor_fk=".$actor." ORDER BY name";
							$result = mysql_query($query, $conn);
							
							while ($row = mysql_fetch_assoc($result))
							{
								echo "\n<property>";
								echo "\n\t<id>".$row["id"]."</id>";
								echo "\n\t<name>".$row["name"]."</name>";
								echo "\n\t<type>".$row["type"]."</type>";
								echo "\n</property>";
							}
							
						}						
						break;
	}
}
else
{
	echo "<error>Type is obligatory</error>";
}

echo "</model>";
?>
