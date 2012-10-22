<?php

$projectPie = new ProjectPie();

$wgHooks['UnknownAction'][] = array($projectPie, 'getXML');
$wgHooks['ArticlePageDataBefore'][] = array($projectPie, 'showLink');

class ProjectPie {

	function showLink($article, $fields){
		global $wgTitle, $wgOut, $wgServer, $wgScriptPath;
		if(($wgTitle->getText() == "Projects" || $wgTitle->getText() == "Themes" || $wgTitle->getText() == "ALL ".PNI || $wgTitle->getText() == "ALL ".CNI) && $wgTitle->getNsText() == "GRAND"){
			$wgOut->addScript("<script type='text/javascript'>
			function mySelect(form){
			form.select();
			}

			function ShowOrHide(d1, d2) {
			if (d1 != '') DoDiv(d1);
			if (d2 != '') DoDiv(d2);
			}

			function DoDiv(id) {
			var item = null;
			if (document.getElementById) {
			item = document.getElementById(id);
			} else if (document.all){
			item = document.all[id];
			} else if (document.layers){
			item = document.layers[id];
			}
			if (!item) {
			}
			else if (item.style) {
			if (item.style.display == 'none'){ item.style.display = ''; }
			else {item.style.display = 'none'; }
			}else{ item.visibility = 'show'; }
			}
			</script>");
			$inner = "";
			$outer = "";
			$filter = "";
			if($wgTitle->getText() == "Projects"){
				$inner = "Projects";
				$outer = "People";
				$filter = "NONE";
			}
			else if($wgTitle->getText() == "Themes"){
				$inner = "Themes";
				$outer = "Projects";
				$filter = "NONE";
			}
			else if($wgTitle->getText() == "ALL ".PNI){
				$inner = "People";
				$outer = "Projects";
				$filter = CNI;
			}
			else if($wgTitle->getText() == "ALL ".CNI){
				$inner = "People";
				$outer = "Projects";
				$filter = PNI;
			}
			$wgOut->addHTML("<a href=\"javascript:ShowOrHide('ProjectPie')\">Expand/Collapse Project Pie Chart</a><br /><a href='$wgServer$wgScriptPath/WedgeStackChart.swf?domain=$wgServer$wgScriptPath&inner=$inner&outer=$outer&filter=$filter' target='_blank'>Full Screen Mode</a><br /><div id='ProjectPie' style='display:none'><embed src='$wgServer$wgScriptPath/WedgeStackChart.swf?domain=$wgServer$wgScriptPath&inner=$inner&outer=$outer&filter=$filter' width='100%' height='600' /></a></div><br />");
		}
		return true;
	}

	function getXML($action, $request){
		$userTable = getTableName("user");
		$ugTable = getTableName("user_groups");
		$nsTable = getTableName("an_extranamespaces");
	
		$filter = "";
		if($action == "getProjects"){
			$show = "AND u.user_name NOT IN (SELECT DISTINCT u1.user_name FROM $userTable u1, $ugTable g1 WHERE g1.ug_user = u1.user_id AND g1.ug_group = '".HQP."')";
			if($_GET['show'] == "CR"){
				$show = "AND u.user_name NOT IN (SELECT DISTINCT u1.user_name FROM $userTable u1, $ugTable g1 WHERE g1.ug_user = u1.user_id AND (g1.ug_group = '".PNI."' OR g1.ug_group = '".HQP."'))";
			}
			else if($_GET['show'] == "NI"){
				$show = "AND u.user_name NOT IN (SELECT DISTINCT u1.user_name FROM $userTable u1, $ugTable g1 WHERE g1.ug_user = u1.user_id AND (g1.ug_group = '".CNI."' OR g1.ug_group = '".HQP."'))";
			}
			$innerFilter = $_GET['innerFilter'];
			$inner = $_GET['inner'];
			$outer = $_GET['outer'];
			if($inner == "projects"){
				if($innerFilter != ""){
					$filter = "AND nsName = '$innerFilter'";
				}
				$sql = "SELECT * FROM $nsTable WHERE themes <> '' $filter";
				$data = DBFunctions::execSQL($sql);
				foreach($data as $row){
					$nsName = str_ireplace("Project_", "", $row['nsName']);
					if($nsName != ""){ 
						if($outer == "people"){
							$sql = "SELECT * 
								 FROM $userTable u, $ugTable g 
							 	 WHERE g.ug_user = u.user_id 
									AND g.ug_group = '$nsName'
									AND u.user_name <> 'Adrian.Sheppard' $show";
							$data2 = DBFunctions::execSQL($sql);
							if(count($data2) > 2){
								echo "<inner name='$nsName' fullName='{$row['fullName']}' themes='{$row['themes']}'>\n";
								foreach($data2 as $row2){
									$sql = "SELECT g.ug_group FROM $ugTable g WHERE g.ug_user = '{$row2['user_id']}'";
									$data3 = DBFunctions::execSQL($sql);
									$type = "";
									foreach($data3 as $row3){
										if($row3['ug_group'] == CNI){
											$type = CNI;
										}
										else if($row3['ug_group'] == PNI){
											$type = PNI;
										}
									}
									echo "<outer name='{$row2['user_name']}' type='$type' count='1' />\n";
								}
								echo "</inner>\n";
							}
						}
						else if($outer == "themes"){
							$sql = "SELECT ns.themes FROM $nsTable ns WHERE ns.nsName = '$nsName'";
							$data2 = DBFunctions::execSQL($sql);
							echo "<inner name='$nsName' fullName='{$row['fullName']}' themes='{$row['themes']}'>\n";
							foreach($data2 as $row2){
								for($i=1; $i <= 5; $i++){
									$themes = split("\n", $row2['themes']);
									$perc = str_replace("\r", "", $themes[$i-1]);
									if($perc != "0" && $perc != "xx"){
										$fullName = $this->getThemeFullName($i);
										echo "<outer name='{$this->getThemeName($i)}' fullName='$fullName' count='$perc' />\n";
									}
								}
							}
							echo "</inner>";
						}
					}
				}
				exit;
			}
			else if($inner == "people"){
				if($innerFilter != ""){
					$filter = "AND u.user_name = '$innerFilter'";
				}
				$sql = "SELECT DISTINCT u.user_name, u.user_id FROM $userTable u, $ugTable g, $nsTable n
						WHERE g.ug_user = u.user_id 
							AND g.ug_group = n.nsName
							AND n.themes <> ''
							AND u.user_name <> 'Adrian.Sheppard' $show
							$filter";
				$data = DBFunctions::execSQL($sql);
				foreach($data as $row){
					$sql = "SELECT g.ug_group FROM $ugTable g WHERE g.ug_user = '{$row['user_id']}'";
					$data3 = DBFunctions::execSQL($sql);
					$type = "";
					foreach($data3 as $row3){
						if($row3['ug_group'] == CNI){
							$type = CNI;
						}
						else if($row3['ug_group'] == PNI){
							$type = PNI;
						}
					}
					if($outer == "projects"){
						echo "<inner name='{$row['user_name']}' type='$type'>\n";
						$sql = "SELECT ns.nsName, ns.themes, ns.fullName
							 FROM $userTable u, $ugTable g, $nsTable ns 
							 WHERE ns.themes <> ''
								AND g.ug_user = u.user_id 
								AND u.user_name = '{$row['user_name']}'
								AND ns.nsName = g.ug_group";
							$data2 = DBFunctions::execSQL($sql);
							foreach($data2 as $row2){
								$nsName = str_ireplace("Project_", "", $row2['nsName']);
								echo "<outer name='$nsName' count='1' fullName='{$row2['fullName']}' themes='{$row2['themes']}' />\n";
							}
						echo "</inner>\n";
					}
					else if($outer == "themes"){
						$sql = "SELECT ns.themes FROM $nsTable ns, $userTable u, $ugTable g 
								WHERE ns.themes <> '' AND ns.nsName = g.ug_group AND g.ug_user = u.user_id AND u.user_id = '{$row['user_id']}'";
						$data2 = DBFunctions::execSQL($sql);
						echo "<inner name='{$row['user_name']}' type='$type'>\n";
						$themeCount = array();
						$themeCount[0] = 0;
						$themeCount[1] = 0;
						$themeCount[2] = 0;
						$themeCount[3] = 0;
						$themeCount[4] = 0;
						foreach($data2 as $row2){
							$themes = split("\n", $row2['themes']);
							for($i = 1; $i <= 5; $i++){
								$perc = str_replace("\r", "", $themes[$i-1]);
								if($perc != "0" && $perc != "xx"){
									//$nsName = str_ireplace("Project_", "", $row['nsName']);
									$themeCount[$i-1] += $perc;
								}
							}
						}
						for($i = 1; $i <= 5; $i++){
							if($themeCount[$i-1] > 0){
								$fullName = $this->getThemeFullName($i);
								echo "<outer name='{$this->getThemeName($i)}' fullName='$fullName' count='1' />\n";
							}
						}
						echo "</inner>";
					}
				}
				exit;
			}
			else if($inner == "themes"){
				$upper = 5;
				$lower = 1;
				if($innerFilter != ""){
					$upper = str_replace("Theme ", "", $innerFilter);
					$lower = $upper;
				}
				for($i = $lower; $i <= $upper; $i++){
					$fullName = $this->getThemeFullName($i);
					echo "<inner name='{$this->getThemeName($i)}' fullName='$fullName'>\n";
					if($outer == "projects"){
						$sql = "SELECT DISTINCT ns.themes, ns.nsName, ns.fullName
							FROM $ugTable g, $nsTable ns 
							WHERE ns.themes <> ''
								AND ns.nsName = g.ug_group";
						$data = DBFunctions::execSQL($sql);
						foreach($data as $row){
							$themes = split("\n", $row['themes']);
							$perc = str_replace("\r", "", $themes[$i-1]);
							if($perc != "0" && $perc != "xx"){
								$nsName = str_ireplace("Project_", "", $row['nsName']);
								echo "<outer name='$nsName' fullName='{$row['fullName']}' count='$perc' />\n";
							}
						}
					}
					if($outer == "people"){
						$sql = "SELECT DISTINCT ns.themes, u.user_name, u.user_id
							FROM $ugTable g, $nsTable ns, $userTable u
							WHERE ns.themes <> ''
								AND ns.nsName = g.ug_group
								AND g.ug_user = u.user_id 
								AND u.user_name <> 'Adrian.Sheppard' $show";
						$data = DBFunctions::execSQL($sql);
						$alreadyIn = null;
						$alreadyIn[] = " ";
						foreach($data as $row){
							$themes = split("\n", $row['themes']);
							$perc = str_replace("\r", "", $themes[$i-1]);
							if($perc != "0" && $perc != "xx" && array_search($row['user_name'], $alreadyIn) == false){
								$alreadyIn[] = $row['user_name'];
								$sql = "SELECT g.ug_group FROM $ugTable g WHERE g.ug_user = '{$row['user_id']}'";
								$data3 = DBFunctions::execSQL($sql);
								$type = "";
								foreach($data3 as $row3){
									if($row3['ug_group'] == CNI){
										$type = CNI;
									}
									else if($row3['ug_group'] == PNI){
										$type = PNI;
									}
								}
								echo "<outer name='{$row['user_name']}' type='$type' count='1' />\n";
							}
						}
					}
					echo "</inner>\n";
				}
				exit;
			}
			return false;
		}
		return true;
	}
	
	private function getThemeFullName($theme){
		if($theme == 1){
			$fullName = "New Media Challenges and Opportunities";
		}
		else if($theme == 2){
			$fullName = "Games and Interactive Simulation";
		}
		else if($theme == 3){
			$fullName = "Animation, Graphics, and Imaging";
		}
		else if($theme == 4){
			$fullName = "Social, Legal, Economic, and Cultural Perspectives";
		}
		else if($theme == 5){
			$fullName = "Enabling Technologies and Methodologies";
		}
		return $fullName;
	}

	private function getThemeName($theme){
		if($theme == 1){
			$name = "nMEDIA";
		}
		else if($theme == 2){
			$name = "GamSim";
		}
		else if($theme == 3){
			$name = "AnImage";
		}
		else if($theme == 4){
			$name = "SocLeg";
		}
		else if($theme == 5){
			$name = "TechMeth";
		}
		return $name;
	}

}

?>
