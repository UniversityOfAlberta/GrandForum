<?php
        /**used to transfer data from centralrepo to main database.**/
    function update($oldpaper, $newperson, $authors,$oldrow){
        $paper = $oldpaper;
        if($paper == null || $paper->getTitle() == ""){
            print_r("This product does not exist");
        }
        $paper->date = $oldrow['cover_date'];
	array_push($authors,$newperson);
        $paper->authors = $authors;
        $status = $paper->update();
        if(!$status){
            print_r("The product <i>{$paper->getTitle()}</i> could not be updated");
        }
	print_r("updated: $updaterow");
    }
    function createnew($newrow,$newperson){
        $paper = new Paper(array());
        $paper->title = $newrow['article_title'];
        $paper->category = "Publication";
        $paper->type = "Misc";
        $paper->description = $newrow['abstract'];
        $paper->date = $newrow['publication_year'];
        $paper->status = "Published";
        $paper->authors = array($newperson);
          //creating new array for data
	$paperdata = array();
	$paperdata['pages'] = $newrow['page_range'];
	$paper->data = $paperdata;
        $paper->access_id = 0;
        $paper->access = "Public";
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
     //create connection
    $conn = new mysqli($servername, $username, $password);
    if($conn->connect_error){
        echo($conn->connect_error);
    }
    else{
        print_r("connected");
        $sql = "SELECT * FROM dev.author, dev.article, dev.author_wrote_article 
		WHERE dev.author.id_number = dev.author_wrote_article.author_id AND
	        dev.article.article_id_number = dev.author_wrote_article.article_id_number;";
        $result = $conn->query($sql);
        if ($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
		print_r($row);
		  //try with fullname
                $fullname=$row['full_name'];
                $person = Person::newFromName($fullname);
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
                            $id = $fullname;
                        }
                    }
		}
		$title = $row["article_title"];
		  //checking if paper exists
		$oldpaper = Paper::newFromTitle($title);
		if($oldpaper->getId() != 0){
		      //checking if author already in authors list
		    $authorsArray=array();
		      //grabbing id from each author
		    foreach ($oldpaper->getAuthors() as $person){
		       array_push($authorsArray,$person->getId());
		    }
		    if(!in_array($id,$authorsArray)){
			print_r("trying to update with");
			update($oldpaper,$person,$oldpaper->getAuthors(),$row);
		    }
		    continue;
		}
		print_r("CREATING NEWW!!!!!");
		createnew($row,$person);
            }
        }
    }
?>
