<?php


abstract class PaperAPI extends API{

    var $update;
    var $type;
    var $category;

    function PaperAPI($update=false, $type, $category){
        $this->update = $update;
        $this->type = $type;
        $this->category = $category;
        $this->addPOST("title",true,"The title of the referenced item to be imported","My Title");
        $this->addPOST("authors",true,"The list of authors who contributed to this item.  The list should be a String in the form \"First Last, First Last, First Last, ...\"","Author1, Author2");
        $this->addPOST("projects",true,"The list of projects associated with this item.  The list should be in the form \"PROJECT1, PROJECT2, PROJECT3, ...\"","MEOW, NAVEL");
        $this->addPOST("misc_type",false,"A custom type for this item","My Custom Type");
    }
    
    function isLoginRequired(){
		return true;
	}

    function processParams($params){
        if(isset($_POST['projects']) && $_POST['projects'] != null){
            $_POST['projects'] = @explode(", ", $_POST['projects']);
        }
        if(isset($_POST['authors']) && $_POST['authors'] != null){
            $_POST['authors'] = @explode(", ", $_POST['authors']);
        }
    }

	function doAction($doEcho=true){
		return $this->insertPaper($doEcho);
	}
	
	function stripQuotes($string){
		$string = str_replace("'", "&#39;", $string);
		return $string;
	}
	
	function insertPaper($doEcho=true){
	    global $wgUser, $wgServer, $wgScriptPath, $wgOut;
	    $me = Person::newFromId($wgUser->getId());
	    $title = @stripslashes($this->stripQuotes($_POST['title']));
	    $new_title = (isset($_POST['new_title']))? @stripslashes($this->stripQuotes($_POST['new_title'])) : $title;
		$product_id = @$_POST['product_id'];
	    $authors = array();
	    if(isset($_POST['authors']) && count($_POST['authors']) > 0 && is_array($_POST['authors'])){
	        foreach(@$_POST['authors'] as $author){
	            $authors[] = @stripslashes($this->stripQuotes($author));
	        }
	    }
	    $projects = array();
	    if(isset($_POST['projects']) && count($_POST['projects']) > 0 && is_array($_POST['projects'])){
	        foreach(@$_POST['projects'] as $project){
	            $projects[] = @stripslashes($this->stripQuotes($project));
	        }
	    }
	    $date = @stripslashes($this->stripQuotes($_POST['date']));
	    $venue = @stripslashes($this->stripQuotes($_POST['venue']));
	    $status = @stripslashes($this->stripQuotes($_POST['status']));
	    if(isset($_POST['abstract'])){
	        $description = @stripslashes($this->stripQuotes($_POST['abstract']));
	    }
	    else{
	        $description = @stripslashes($this->stripQuotes($_POST['description']));
	    }
	    
	    $data = array();
	    foreach($this->posts as $post){
	        if($post['name'] != "title" &&
	           $post['name'] != "authors" &&
	           $post['name'] != "projects" &&
	           $post['name'] != "date" &&
	           $post['name'] != "status" &&
	           $post['name'] != "description" &&
	           $post['name'] != "abstract" &&
	           $post['name'] != "misc_type"){
          // Prevent empty parameters in DB:
					if (isset($_POST[$post['name']]) && trim($_POST[$post['name']])!=='')
	            $data[$post['name']] = @stripslashes($this->stripQuotes($_POST[$post['name']]));
	        }
	    }

        if(isset($_GET['create']) && !isset($_GET['edit'])){
	        $paper = null;
	    }
	    else{
	        $paper = Paper::newFromTitle($title, $this->category);
	    }
	    if(strstr($this->type, "Misc") !== false && isset($_POST['misc_type'])){
            $type = "Misc: ".str_replace("'", "&#39", $_POST['misc_type']);
        }
        else {
            $type = $this->type;
        }
	    
	    if($this->update && $paper != null && $paper->getTitle() != null){
	        // Already exists, so just update the old data
	        $sql = "UPDATE grand_products
					SET
	                description = '".$description."',
	                projects = '".serialize($projects)."',
					title = '{$new_title}',
	                type = '{$type}',
	                date = '$date',
	                venue = '$venue',
	                status = '$status',
	                authors = '".serialize($authors)."',
	                data = '".serialize($data)."'
	                WHERE id = '$product_id'";
	        $result = DBFunctions::execSQL($sql, true);
	         
	        Paper::$cache = array();
	        $paperAfter = Paper::newFromId($product_id);
	        // Notification for new authors
	        foreach($paperAfter->getAuthors() as $author){
                $found = false;
                foreach($paper->getAuthors() as $author1){
                    if($author->getId() == $author1->getId()){
                        $found = true;
                        break;
                    }
                }
                if($found == false){
                    Notification::addNotification($me, $author, "{$this->category} Author Added", "You have been added as an author to a ".strtolower($this->category)." entitled <i>{$paper->getTitle()}</i>", "{$paper->getUrl()}");
                }
                else{
                    // Generic change to publication
                    Notification::addNotification($me, $author, "{$this->category} Modified", "Your ".strtolower($this->category)." entitled <i>{$paper->getTitle()}</i> has been modified", "{$paper->getUrl()}");
                }
	        }
	        // Notification for removed authors
	        foreach($paper->getAuthors() as $author){
                $found = false;
                foreach($paperAfter->getAuthors() as $author1){
                    if($author->getId() == $author1->getId()){
                        $found = true;
                        break;
                    }
                }
                if($found == false){
                    Notification::addNotification($me, $author, "{$this->category} Author Removed", "You have been removed as an author to the ".strtolower($this->category)." entitled <i>{$paper->getTitle()}</i>", "{$paper->getUrl()}");
                }
	        }
	        $paperAfter->syncAuthors();
	    }
	    else{
	        $sql = "INSERT INTO grand_products (`description`,`category`,`projects`,`type`,`title`,`date`,`venue`,`status`,`authors`,`data`)
	                VALUES ('$description','{$this->category}','".serialize($projects)."','{$type}','$title','$date','$venue','$status','".serialize($authors)."','".serialize($data)."')";
	        $result = DBFunctions::execSQL($sql, true);
	        Paper::$cache = array();
	        $paper = Paper::newFromTitle($title, $this->category, $type, $status);
	        foreach($authors as $author){
	            $person = Person::newFromNameLike($author);
                if($person == null || $person->getName() == null){
                    // The name might not match exactly what is in the db, try aliases
                    try{
                        $person = Person::newFromAlias($author);
                    }
                    catch(DomainException $e){
                        $person = null;
                    }
                }
                if($person != null && $person->getName() != null){
                    Notification::addNotification($me, $person, "{$this->category} Created", "A new ".strtolower($this->category).", entitled <i>{$paper->getTitle()}</i>, has been created with yourself listed as one of the authors", "{$paper->getUrl()}");
                }
	        }
	        $paper->syncAuthors();
	    }
	    
	    $string = "";
	    if($result == 1 && $paper != null && $paper->getTitle() != null){
	        $string = "{$type} '{$_POST['title']}' was modified successfully\n";
	    }
	    else if($result == 1){
	        $string = "{$type} '{$_POST['title']}' was added successfully\n";
	    }
	    else{
	        $string = "{$type} '{$_POST['title']}' was not added successfully\n";
	    }
	    if($doEcho){
	        echo $string;
	    }
	    else{
	        return $string;
	    }
	}
}
?>
