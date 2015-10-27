<?php
        /**used to transfer data from centralrepo to main database.**/
    function update($oldpaper, $newperson, $authors,$oldrow){
        $paper = $oldpaper;
        if($paper == null || $paper->getTitle() == ""){
            print_r("This product does not exist");
        }
	array_push($authors,$newperson);
        $paper->authors = $authors;
        $status = $paper->update();
        if(!$status){
            print_r("The product <i>{$paper->getTitle()}</i> could not be updated");
        }
    }
    function createnew($newrow,$newperson){
        $paper = new Paper(array());
        $paper->title = $newrow['article_title'];
        $paper->category = "Publication";
        $paper->type = "Misc";
        $paper->description = $newrow['abstract'];
        $paper->date = $newrow['cover_date']. " 00:00:00";
        $paper->status = "Published";
        $paper->authors = array($newperson);
          //creating new array for data
	$paperdata = array();
	$paperdata['pages'] = $newrow['page_range'];
	$paper->data = $paperdata;
        $paper->access_id = 0;
        $paper->access = "Public";
	$paper->central_repo_id = $newrow['article_id_number'];
        $status = $paper->create();
        if(!$status){
            print_r("The product <i>{$paper->getTitle()}</i> could not be created");
        }
        $paper = Product::newFromId($paper->getId());
    }
    require_once( "commandLine.inc" );
    
    $wgUser=User::newFromName("Admin");
    $servername = "199.116.235.47";
    $username = "new_root";
    $password = "shoutTEARstreamTAIL";
    $DB= "fospubs";
     //create connection
    $conn = new mysqli($servername, $username, $password);
    if($conn->connect_error){
        echo($conn->connect_error);
    }
    else{
        print_r("connected");
        $sql = "SELECT * FROM $DB.author, $DB.article, $DB.author_wrote_article 
		WHERE $DB.author.id_number = $DB.author_wrote_article.author_id AND
	        $DB.article.article_id_number = $DB.author_wrote_article.article_id_number;";
        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
		  //try with fullname
                $fullname=$row['full_name'];
                $person = Person::newFromNameLike($fullname);
                $id = $person->getId();
		  //if fullname is empty try email
                if($id == 0){
                    $email = $row['email'];
                    $person = Person::newFromEmail($email);
                    if($person != null){
                        $id = $person->getId();
			  //if email is empty just use name
                        if($id == 0){
			    $person->name = $fullname;
                            print_r($person);
			    $id = $fullname;
                        }
                    }
		}
		$title = $row["article_title"];
		  //checking if paper exists
		$oldpaper = Paper::newFromTitle($title);
		if($oldpaper->getId() != 0 && $oldpaper->getCentralRepoId() != 0){
		      //checking if author already in authors list
		    $authorsArray=array();
		      //grabbing id from each author
		    foreach ($oldpaper->getAuthors() as $old){
			$oldId = $old->getId();
			if($oldId == 0){
			     $oldId == $old->getId();
			}
                        array_push($authorsArray,$oldId);
		    }
		    if(!in_array($id,$authorsArray)){
			print_r("UPDATEEEEE!!!!!!! trying to update with $title $id". "\n");
			//print($person);
			update($oldpaper,$person,$oldpaper->getAuthors(),$row);
		    }
		    continue;
		}
		        $idd =  $row['cover_date']. " 00:00:00";
		//print($person);
		print_r("CREATING NEWW!!!!! $title $idd" . "\n");
		createnew($row,$person);
            }
        }
    }
?>
