<?php

class AddMaterialAPI extends API{

    var $errors = "";
    var $typeSet = false;

    function __construct(){
        $this->addPOST("id", false, "The id of the Material(only required if updating)","5");
        $this->addPOST("type", false, "The type of Material this is.  If set, this overides the inferred type based on the uploaded file", "video");
        $this->addPOST("title", true, "The title of the Material","My Material");
        $this->addPOST("date", true, "The date of the Material(MM-DD-YYYY)","05-13-2012");
        $this->addPOST("media", true, "The location of the media file", "http://faculty.arts.ubc.ca/lfreund/img/Grand_Logo_4C_JPG_168KB.jpg");
        $this->addPOST("url", false, "A website URL relevant to the media", "http://faculty.arts.ubc.ca/lfreund/research.htm");
        $this->addPOST("users", true, "The user names of the users involved with this Material, separated by commas","First1.Last1, First2.Last2, First3.Last3");
        $this->addPOST("projects", true, "The projects involved with this Material, separated by commas","MEOW, NAVEL");
        $this->addPOST("keywords", true, "The keywords associated with this Material, separated by commas","Scientific, Health");
        $this->addPOST("description", true, "The description of the Material", "This is the description of my Material");
    }

    function processParams($params){
        $users = explode(",", $_POST['users']);
        $_POST['users'] = array();
        foreach($users as $user){
            $person = Person::newFromName(trim($user))->getId();
            if($person != null && $person->getName() != null){
                $_POST['users'][] = $person->getId();
            }
            else{
                $_POST['users'][] = trim($user);
            }           
        }
        $projects = explode(",", $_POST['projects']);
        $_POST['projects'] = array();
        foreach($projects as $project){
            $_POST['projects'][] = Project::newFromName(trim($project))->getId();
        }
        if(isset($_POST['keywords'])){
            $keywords = explode(",", $_POST['keywords']);
            $_POST['keywords'] = array();
            foreach($keywords as $keyword){
                $_POST['keywords'][] = str_replace("'", "&#39;", trim($keyword));
            }
        }
        else{
            $_POST['keywords'] = array();
        }
    }

	function doAction($noEcho=false){
		global $wgRequest, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
		$groups = $wgUser->getGroups();
		$me = Person::newFromId($wgUser->getId());
        if(!isset($_POST['projects']) || count($_POST['projects']) == 0){
            $_POST['projects'] = array();
        }
        if(!isset($_POST['users']) || count($_POST['users']) == 0){
            $_POST['users'] = array();
        }
        $_POST['url'] = str_replace("'", "&#39;", $_POST['url']);
        $_POST['title'] = str_replace("'", "&#39;", $_POST['title']);
        $_POST['date'] = str_replace("'", "&#39;", $_POST['date']);
        if(isset($_POST['type'])){
            $this->typeSet = true;
            $_POST['type'] = str_replace("'", "&#39;", $_POST['type']);
        }
		if(isset($_POST['id'])){
		    if($_POST['title'] == ""){
	            $string = "The Multimedia must not have an empty title";
	            $wgMessage->addError($string);
	            return $string;
	        }
		    if($this->typeSet && $_POST['type'] == 'form'){
		        $material = Form::newFromId($_POST['id']);
		    }
		    else{
		        $material = Material::newFromId($_POST['id']);
		    }
		    //Updating
		    $typeSQL = "";
		    if($this->typeSet){
		        $typeSQL = "`type` = '{$_POST['type']}',";
		    }
		    $sql = "UPDATE grand_materials
                        SET `title` = '{$_POST['title']}',
                            {$typeSQL}
                            `date` = '{$_POST['date']}',
                            `description` = '".str_replace("'", "&#39;", $_POST['description'])."',
                            `url` = '{$_POST['url']}'
                        WHERE id = '{$material->getId()}'";
            $status = DBFunctions::execSQL($sql, true);
            Material::$cache = array();
            if($this->typeSet && $_POST['type'] == 'form'){
                $materialAfter = Form::newFromId($_POST['id']);
            }
            else{
                $materialAfter = Material::newFromId($_POST['id']);
            }
	        $this->updateMedia($materialAfter);
	        $this->updatePeople($materialAfter);
	        $this->updateProjects($materialAfter);
	        $this->updateKeywords($materialAfter);
	        Material::$cache = array();
	        if($this->typeSet && $_POST['type'] == 'form'){
                $materialAfter = Form::newFromId($_POST['id']);
            }
            else{
                $materialAfter = Material::newFromId($_POST['id']);
            }
		}
		else{
		    //Inserting
		    if($_POST['title'] == ""){
	            $string = "The Multimedia must not have an empty title";
	            $wgMessage->addError($string);
	            return $string;
	        }
		    if(!$this->typeSet){
		        $_POST['type'] = 'other';
		    }
		    $sql = "INSERT INTO grand_materials
                        (`title`,`type`,`date`,`description`,`url`)
                        VALUES ('{$_POST['title']}','{$_POST['type']}','{$_POST['date']}','".str_replace("'", "&#39;", $_POST['description'])."','{$_POST['url']}')";
            DBFunctions::execSQL($sql, true);
            Material::$cache = array();
            if($this->typeSet && $_POST['type'] == 'form'){
                $material = Form::newFromTitle($_POST['title']);
            }
            else{
                $material = Material::newFromTitle($_POST['title']);
            }
            $this->updateMedia($material);
            $this->updatePeople($material);
            $this->updateProjects($material);
            $this->updateKeywords($material);
            Material::$cache = array();
            if($this->typeSet && $_POST['type'] == 'form'){
                $material = Form::newFromTitle($_POST['title']);
            }
            else{
                $material = Material::newFromTitle($_POST['title']);
            }
		}
		DBFunctions::commit();
	}
	
	function updatePeople($material){
	    $people = $material->getPeople();
        foreach($people as $person){
            $found = false;
            foreach($_POST['users'] as $key => $user){
                if($person->getName() == $user){
                    $found = true;
                    unset($_POST['users'][$key]);
                }
            }
            if(!$found){
                $sql = "DELETE FROM `grand_materials_people`
                        WHERE `material_id` = '{$material->getId()}'
                        AND `user_id` = '{$person->getId()}'";
                DBFunctions::execSQL($sql, true);
            }
        }
        if(is_array($_POST['users'])){
            foreach($_POST['users'] as $user){
                $person = Person::newFromNameLike($user);
                if($person != null && $person->getName() != ""){
                    $sql = "INSERT INTO `grand_materials_people`
                                   (`material_id`,`user_id`)
                            VALUES ('{$material->getId()}','{$person->getId()}')";
                    DBFunctions::execSQL($sql, true);
                }
                else{
                    $this->errors .= "User '$user' does not exist.<br />\n";
                }
            }
        }
	}
	
	function updateProjects($material){
	    $projects = $material->getProjects();
        foreach($projects as $project){
            $found = false;
            foreach($_POST['projects'] as $key => $proj){
                if($project->getName() == $proj){
                    $found = true;
                    unset($_POST['projects'][$key]);
                }
            }
            if(!$found){
                $sql = "DELETE FROM `grand_materials_projects`
                        WHERE `material_id` = '{$material->getId()}'
                        AND `project_id` = '{$project->getId()}'";
                DBFunctions::execSQL($sql, true);
            }
        }
        foreach($_POST['projects'] as $proj){
            $project = Project::newFromName($proj);
            if($project != null && $project->getName() != ""){
                $sql = "INSERT INTO `grand_materials_projects`
                               (`material_id`,`project_id`)
                        VALUES ('{$material->getId()}','{$project->getId()}')";
                DBFunctions::execSQL($sql, true);
            }
        }
	}
	
	function updateMedia($material){
	    global $wgFileExtensions, $wgUser;
	    if($_POST['media'] == ""){
	        $sql = "UPDATE `grand_materials`
	                SET `mediaLocal` = '',
	                    `media` = '',
	                    `type` = '{$_POST['type']}'
	                WHERE `id` = '{$material->getId()}'";
	        DBFunctions::execSQL($sql, true);
	        return;
	    }
	    $media = $_POST['media'];
	    $exploded = explode(".", $media);
	    $found = false;
	    $extension = "";
	    $_POST['mediaLocal'] = "";
	    if(strstr($_POST['media'], "ttp") === false){
            $_POST['media'] = "http://".$_POST['media'];
        }
        if(!$this->locationExists($_POST['media'])){
            $this->errors = "This url does not exist.<br />\n";
            return;
        }
	    foreach($wgFileExtensions as $ext){
	        if($exploded[count($exploded)-1] == "$ext"){
	            $found = true;
	            $extension = $ext;
	            break;
	        }
	    }
	    if(!$found){
	        if(strstr($media, "youtube") !== false ||
	           strstr($media, "youtu.be") !== false){
	            $_POST['mediaLocal'] = str_replace("youtu.be/", "", $media);
	            $_POST['mediaLocal'] = str_replace("youtube.com/watch?v=", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("http://", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("https://", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("www.", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("youtube.com/watch?v=", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = preg_replace("/&feature=.*/", "", $_POST['mediaLocal']);
	            if(!$this->typeSet){
	                $_POST['type'] = "youtube";
	            }
	        }
	        else if(strstr($media, "vimeo.com") !== false){
	            $_POST['mediaLocal'] = str_replace("player.vimeo.com/video/", "", $media);
	            $_POST['mediaLocal'] = str_replace("vimeo.com/", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("http://", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("https://", "", $_POST['mediaLocal']);
	            $_POST['mediaLocal'] = str_replace("www.", "", $_POST['mediaLocal']);
	            if(!$this->typeSet){
	                $_POST['type'] = "vimeo";
	            }
	        }
	    }
	    else{
	        global $wgRequest;
	        $wgRequest->setVal("wpUploadFileURL", $_POST['media']);
	        $wgRequest->setVal("wpUpload", true);
            $wgRequest->setVal("wpSourceType", 'url');
            $wgRequest->setVal("action", 'submit');
            $wgRequest->setVal("wpDestFile", $material->getId().".$extension");
            $wgRequest->setVal("wpDestFileWarningAck", true);
            $wgRequest->setVal("wpIgnoreWarning", true);
            $wgRequest->setVal("wpEditToken", $wgUser->getEditToken());
	        $upload = new SpecialUpload($wgRequest);
	        $upload->execute(null);
	        //print_r($upload);
	        if($upload->mLocalFile != null){
	            if(!$this->typeSet){
	                $mime = $upload->mLocalFile->getMimeType();
	                if(strstr($mime, "image") !== false){
	                    $_POST['type'] = "img";
	                }
	                else if(strstr($mime, "video") !== false || strstr($mime, "application/ogg") !== false){
	                    // Someday, convert videos to HTML5 formats
	                    $_POST['type'] = "video";
	                }
	                else if(strstr($mime, "audio") !== false){
	                    $_POST['type'] = "audio";
	                }
	                else if(strstr($mime, "pdf") !== false){
	                    $_POST['type'] = "pdf";
	                }
	                else if(strstr($mime, "ppt") !== false ||
	                        strstr($mime, "pptx") !== false){
	                    $_POST['type'] = "ppt";
	                }
	                else if(strstr($mime, "zip") !== false ||
	                        strstr($mime, "rar") !== false ||
	                        strstr($mime, "tgz") !== false ||
	                        strstr($mime, "tar") !== false){
	                    $_POST['type'] = "zip";
	                }
	                else {
	                    $_POST['type'] = "other";
	                }
	            }
	        }
	        else{
	            $this->errors .= "There was a problem with the upload.  Please make sure the URL is correct, and publicly accessible.<br />\n";
	            return;
	        }
	        $_POST['mediaLocal'] = "{$material->getId()}.$extension";
	    }
	    if(!isset($_POST['type'])){
	        $_POST['type'] = 'other';
	    }
	    $sql = "UPDATE `grand_materials`
	            SET `type` = '{$_POST['type']}',
	                `media` = '{$_POST['media']}',
	                `mediaLocal` = '{$_POST['mediaLocal']}'
	            WHERE `id` = '{$material->getId()}'";
	    DBFunctions::execSQL($sql, true);
	}
	
	function updateKeywords($material){
	    $keywords = $material->getKeywords();
        foreach($keywords as $keyword){
            $found = false;
            foreach($_POST['keywords'] as $key => $keyw){
                if($keyword == $keyw){
                    $found = true;
                    unset($_POST['keywords'][$key]);
                }
            }
            if(!$found){
                $sql = "DELETE FROM `grand_materials_keywords`
                        WHERE `material_id` = '{$material->getId()}'
                        AND `keyword` = '{$keyword}'";
                DBFunctions::execSQL($sql, true);
            }
        }
        foreach($_POST['keywords'] as $keyword){
            if($keyword != ""){
                $sql = "INSERT INTO `grand_materials_keywords`
                               (`material_id`,`keyword`)
                        VALUES ('{$material->getId()}','{$keyword}')";
                DBFunctions::execSQL($sql, true);
            }
        }
	}
	
	function locationExists($location){
	    $f1 = $location;
        $file_headers = @get_headers($f1);
        if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
	}
	
	function isLoginRequired(){
		return true;
	}
}

?>
