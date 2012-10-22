<?php
	require_once( 'commandLine.inc' );
	
	if( count( $args ) != 3){
		exit(1);
	}
	$project1 = Project::newFromName($args[0]);
	$project2 = Project::newFromName($args[1]);
	$newProject = $args[2];
	if($project1 != null && $project2 != null){
	    if($project1->getId() != $project2->getId()){
	        $newP = Project::newFromName($newProject);
	        if($newP == null){
	            $sql = "SELECT `nsId` FROM `mw_an_extranamespaces` ORDER BY `nsId` DESC LIMIT 1";
	            $data = DBFunctions::execSQL($sql);
	            if(count($data) == 1){
	                $id = $data[0]['nsId'] + 2;
	                $sql = "INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('$id','$newProject','1')";
	                DBFunctions::execSQL($sql, true);
	                $sql = "INSERT INTO `grand_project` (`id`,`name`) VALUES ('$id','$newProject')";
	                DBFunctions::execSQL($sql, true);
	                $sql = "INSERT INTO `grand_project_descriptions` (`project_id`,`full_name`,`themes`) VALUES ('$id','$newProject','0\\n0\\n0\\n0\\n0')";
	                DBFunctions::execSQL($sql, true);
	                $sql = "INSERT INTO `grand_project_themes` (`project_id`,`name`,`themes`) VALUES ('$id','$newProject','0\n0\n0\n0\n0')";
	                DBFunctions::execSQL($sql, true);
	                echo "Created Project '{$newProject}'\n";
	            }
	            Project::$cache = array();
	            $newP = Project::newFromName($newProject);
	        }
	        if($newP != null){
	            if($project1->getName() != $newProject){
	                $sql = "INSERT INTO `grand_project_evolution` (`project_id`,`new_id`) VALUES ('{$project1->getId()}', '{$newP->getId()}')";
	                DBFunctions::execSQL($sql, true);
	            }
	            if($project2->getName() != $newProject){
	                $sql = "INSERT INTO `grand_project_evolution` (`project_id`,`new_id`) VALUES ('{$project2->getId()}', '{$newP->getId()}')";
	                DBFunctions::execSQL($sql, true);
	            }
	            echo "Merged Projects '{$project1->getName()}' and '{$project2->getName()}'\n";
	        }
	    }
	}
	echo "\n";
?>
